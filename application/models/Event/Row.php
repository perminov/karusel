<?php
class Event_Row extends Indi_Db_Table_Row{
    public function save() {

        // Делаем логи изменений в мероприятиях, но только для уже существующих мероприятий
        if ($this->id && count($this->_modified)) {
            // Находим id текущей сущности
            $entityId = Misc::loadModel('Entity')->fetchRow('`table` = "' . $this->getTable()->info('name') . '"')->id;

            // Обуляем изменения, предварительно селав бакап
            $modified = $this->_modified; $this->_modified = array(); unset($modified['price'], $modified['clientAgreementNumber']);

            // Выдергиваем записи 'Было' по внешним ключам
            $was = clone $this; $was->setForeignRowsByForeignKeys(implode(',', array_keys($modified)));

            // Выдергиваем записи 'Стало' по внешним ключам
            $this->_modified = $modified;
            $now = clone $this; $now->setForeignRowsByForeignKeys(implode(',', array_keys($modified)));

            $adjustmentM = Misc::loadModel('Adjustment');
            $fieldRs = Misc::loadModel('Field')->fetchAll('`entityId` = "' . $entityId .'" AND FIND_IN_SET(`alias`, "' . implode(',', array_keys($modified)) . '")', 'move');
            foreach ($fieldRs as $fieldR) {
                $adjustmentR = $adjustmentM->createRow();
                $adjustmentR->eventId = $this->id;
                $adjustmentR->fieldId = $fieldR->id;

                if (array_key_exists($fieldR->alias, $was->_original['foreign'])) {
                    if ($was->_original['foreign'][$fieldR->alias] instanceof Indi_Db_Table_Rowset) {
                        $implodedWas = array();
                        foreach ($was->_original['foreign'][$fieldR->alias] as $r) $implodedWas[] = $r->getTitle();
                        $adjustmentR->was = implode(', ', $implodedWas);
                    } else {
                        $adjustmentR->was = $was->_original['foreign'][$fieldR->alias]->getTitle();
                    }
                } else {
                    $adjustmentR->was = $was->{$fieldR->alias};
                }

                if (array_key_exists($fieldR->alias, $now->_original['foreign'])) {
                    if ($now->_original['foreign'][$fieldR->alias] instanceof Indi_Db_Table_Rowset) {
                        $implodedNow = array();
                        foreach ($now->_original['foreign'][$fieldR->alias] as $r) $implodedNow[] = $r->getTitle();
                        $adjustmentR->now = implode(', ', $implodedNow);
                    } else {
                        $adjustmentR->now = $now->_original['foreign'][$fieldR->alias]->getTitle();
                    }
                } else {
                    $adjustmentR->now = $now->{$fieldR->alias};
                }

                $adjustmentR->datetime = date('Y-m-d H:i:s');
                $adjustmentR->authorType = $_SESSION['admin']['alternate'] ? 307 : 11;
                $adjustmentR->author = $_SESSION['admin']['id'];
                $adjustmentR->save();
            }
        }

        // Формируем title
        $districtR = $this->getForeignRowByForeignKey('districtId');
        $placeR = $this->getForeignRowByForeignKey('placeId');
        $timeR = $this->getForeignRowByForeignKey('timeId');
        $title = array();
        $title[] = '[' . $this->date . ', ' . $timeR->getTitle() . ']';
        $title[] = $districtR->code . ': ' . $placeR->title;
        $this->title = implode(' ', $title);

        // Рассчитываем стоимость
        $this->price = $this->getTable()->getAdapter()->query('
            SELECT
                CAST((IF("'. $this->animatorsCount . '" = "1", `price1`, `price2`) -
                    IF("'. $this->animatorsCount . '" = "1",
                        `price1` *
                          IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $this->date . '") NOT IN(5,6), 1, 0) *
                          ((CAST(`s`.`detailsString` AS DECIMAL))/100),
                        `price2` *
                          IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $this->date . '") NOT IN(5,6), 1, 0) *
                          ((CAST(`s`.`detailsString` AS DECIMAL))/100)
                    )) AS DECIMAL)
                AS `price`
            FROM
                `district` `d`,
                `place` `p`
                LEFT JOIN `holiday` `h` ON (`h`.`title` = "' . $this->date . '")
                LEFT JOIN `staticblock` `s` ON (`s`.`alias` = "work-day-discount")
            WHERE 1
                AND `p`.`id` = "' . $this->placeId . '"
                AND `d`.`id` = `p`.`districtId`
        ')->fetchColumn(0);

        // Для новых завок указываем кто их создал и когда
        if (!$this->id) {
            if ($_SESSION['admin']['alternate'] == 'manager') {
                $this->requestBy = 'manager';
                $this->requestByManagerId = $_SESSION['admin']['id'];
                $this->requestDate = date('Y-m-d H:i:s');
            } else {
                $this->requestBy = 'client';
                $this->requestDate = date('Y-m-d H:i:s');
            }
        } else {
            unset($this->_modified['requestBy'], $this->_modified['requestByManagerId'], $this->_modified['requestDate']);
        }

        // Формируем calendarStart и calendarEnd
        $this->calendarStart = $this->date . ' ' . $timeR->title . ':00';
        $this->calendarEnd = $this->getTable()->getAdapter()->query('
            SELECT DATE_ADD(TIMESTAMP("' . $this->calendarStart . '"), INTERVAL ' . $placeR->duration . ' MINUTE)
        ')->fetchColumn(0);

        parent::save();

        // Забиваем аниматоров
        $animators = explode(',', $this->animatorIds);
        $eaM = Misc::loadModel('EventAnimator');
        $eaM->fetchAll('`eventId` = "' . $this->id . '"')->delete();
        for ($i = 0; $i < count($animators); $i++) {
            if ($animators[$i]) {
                $eaR = $eaM->createRow();
                $eaR->eventId = $this->id;
                $eaR->animatorId = $animators[$i];
                $eaR->save();
            }
        }
    }

    public function setAgreementNumber(){
        // Конструируем номер договора
        $districtR = $this->getForeignRowByForeignKey('districtId');
        $agreementsCount = $this->getTable()->getAdapter()->query('SELECT COUNT(`id`) FROM `event` WHERE `clientAgreementNumber` != ""')->fetchColumn(0);
        $this->clientAgreementNumber = $districtR->code . str_pad($agreementsCount + 1, 4, '0', STR_PAD_LEFT);
        parent::save();
    }
}