<?php
class Event_Row extends Indi_Db_Table_Row {
    public function save() {

        // Делаем логи изменений в мероприятиях, но только для уже существующих мероприятий
        if ($this->id && count($this->_modified)) {

            // Находим id текущей сущности
            $entityId = $this->model()->id();

            // Обуляем изменения, предварительно селав бакап
            $modified = $this->_modified; $this->_modified = array(); unset($modified['price'], $modified['clientAgreementNumber']);

            // Получаем список внешних ключей
            $foreignA = $this->model()->fields()->select('one,many', 'storeRelationAbility')->column('alias');

            // Получаем список измененных внешних ключей
            $modifiedForeignA = array_intersect($foreignA, array_keys($modified));

            // Выдергиваем записи 'Было' по внешним ключам
            $was = clone $this; $was->foreign(implode(',', $modifiedForeignA));

            // Выдергиваем записи 'Стало' по внешним ключам
            $this->_modified = $modified;

            // Конвертируем значения, имеющиеся в _modified в формат, пригодный для работы с ->foreign()
            $this->mismatch(true);

            $now = clone $this; $now->foreign(implode(',', $modifiedForeignA));

            $adjustmentM = Indi::model('Adjustment');
            $fieldRs = Indi::model('Field')->fetchAll('`entityId` = "' . $entityId .'" AND FIND_IN_SET(`alias`, "' . implode(',', array_keys($this->_modified)) . '")', 'move');
            foreach ($fieldRs as $fieldR) {
                $adjustmentR = $adjustmentM->createRow();
                $adjustmentR->eventId = $this->id;
                $adjustmentR->fieldId = $fieldR->id;

                if (array_key_exists($fieldR->alias, $was->foreign())) {
                    if ($was->foreign($fieldR->alias) instanceof Indi_Db_Table_Rowset) {
                        $implodedWas = array();
                        foreach ($was->foreign($fieldR->alias) as $r) $implodedWas[] = $r->title();
                        $adjustmentR->was = implode(', ', $implodedWas);
                    } else if ($now->foreign($fieldR->alias) instanceof Indi_Db_Table_Row) {
                        $adjustmentR->was = $was->foreign($fieldR->alias)->title();
                    }
                } else {
                    $adjustmentR->was = $was->{$fieldR->alias};
                }

                if (array_key_exists($fieldR->alias, $now->foreign())) {
                    if ($now->foreign($fieldR->alias) instanceof Indi_Db_Table_Rowset) {
                        $implodedNow = array();
                        foreach ($now->foreign($fieldR->alias) as $r) $implodedNow[] = $r->title();
                        $adjustmentR->now = implode(', ', $implodedNow);
                    } else if ($now->foreign($fieldR->alias) instanceof Indi_Db_Table_Row) {
                        $adjustmentR->now = $now->foreign($fieldR->alias)->title();
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
        $districtR = $this->foreign('districtId');
        $placeR = $this->foreign('placeId');
        $timeR = $this->foreign('timeId');
        $title = array();
        $title[] = '[' . $this->date . ', ' . $timeR->title . ']';
        $title[] = $districtR->code . ': ' . $placeR->title;
        $this->title = implode(' ', $title);

        // Рассчитываем возраст именинника
        if ($this->_modified['birthChildBirthDate']) {
            if ($this->_modified['birthChildBirthDate'] == '0000-00-00') {
                $this->birthChildAge = 0;
            } else {
                $this->birthChildAge = date('Y') - date('Y', strtotime($this->_modified['birthChildBirthDate']));
            }
        }

        if (is_array($this->animatorIds)) $this->animatorIds = implode(',', $this->animatorIds);

        // Рассчитываем стоимость
        if (!trim($this->animatorIds)) {
            if ($this->subprogramId) {
                $animatorsCount = $this->foreign('subprogramId')->animatorsCount;
            } else {
                $animatorsCount = 1;
            }
        } else {
            $animatorsCount = count(explode(',', $this->animatorIds));
        }
        $this->price = Indi::db()->query('
                SELECT
                  CAST((IF("'. $animatorsCount . '" = "1", `price1`, `price2`) -
                  IF("'. $animatorsCount . '" = "1",

                    `price1` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $this->date . '") NOT IN(5,6) AND IF(ISNULL(`s`.`detailsString`), 0, 1) AND IF(ISNULL(`s2`.`detailsString`), 1, `t`.`title` < `s2`.`detailsString`), 1, 0) *
                      (IF(ISNULL(`s`.`detailsString`), 0, CAST(`s`.`detailsString` AS DECIMAL))/100),

                    `price2` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $this->date . '") NOT IN(5,6) AND IF(ISNULL(`s`.`detailsString`), 0, 1) AND IF(ISNULL(`s2`.`detailsString`), 1, `t`.`title` < `s2`.`detailsString`), 1, 0) *
                      (IF(ISNULL(`s`.`detailsString`), 0, CAST(`s`.`detailsString` AS DECIMAL))/100)

                  )) AS DECIMAL)
                  AS `price`
                FROM
                  `district` `d`,
                  `place` `p`,
                  `time` `t`
                  LEFT JOIN `holiday` `h` ON (`h`.`title` = "' . $this->date . '")
                  LEFT JOIN `staticblock` `s` ON (`s`.`alias` = "work-day-discount" AND `s`.`toggle` = "y")
                  LEFT JOIN `staticblock` `s2` ON (`s2`.`alias` = "discount-until-time" AND `s2`.`toggle` = "y")
                WHERE 1
                  AND `p`.`id` = "' . $this->placeId . '"
                  AND `d`.`id` = `p`.`districtId`
                  AND `t`.`id` = "' . $this->timeId . '"
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
        $this->calendarEnd = Indi::db()->query('
            SELECT DATE_ADD(TIMESTAMP("' . $this->calendarStart . '"), INTERVAL ' . $placeR->duration . ' MINUTE)
        ')->fetchColumn(0);

        parent::save();

        // Забиваем аниматоров
        $animators = explode(',', $this->animatorIds);
        $eaM = Indi::model('EventAnimator');
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
        $districtR = $this->foreign('districtId');
        $this->clientAgreementNumber = $districtR->code . str_pad($districtR->lastAgreement + 1, 4, '0', STR_PAD_LEFT);
        $districtR->lastAgreement++;
        $districtR->save();
        parent::save();
    }
}