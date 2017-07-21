<?php
class Event_Row extends Indi_Db_Table_Row_Schedule {
    public function save() {

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

        if (is_array($this->animatorId)) $this->animatorId = implode(',', $this->animatorId);

        // Рассчитываем стоимость
        if (!trim($this->animatorId)) {
            if ($this->subprogramId) {
                $animatorsCount = $this->foreign('subprogramId')->animatorsCount;
            } else {
                $animatorsCount = 1;
            }
        } else {
            $animatorsCount = count(explode(',', $this->animatorId));
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

        return parent::save();
    }

    /**
     * Confirm event
     */
    public function confirm($managerId, $prepay){

        // Shortcut for event's district
        $districtR = $this->foreign('districtId');

        // Assign confirmation-related props
        $this->assign(array(
            'managePrepay' => $prepay,
            'manageManagerId' => $managerId,
            'manageStatus' => '120#00ff00',
            'manageDate' => date('Y-m-d'),
            'clientAgreementNumber' => $districtR->code . str_pad($districtR->lastAgreement + 1, 4, '0', STR_PAD_LEFT)
        ));

        // Save assigned props
        parent::save();

        // Increment agreement counter
        $districtR->lastAgreement++;
        $districtR->save();
    }

    /**
     *
     */
    public function setSpaceSince() {
        $this->spaceSince = $this->date . ' ' . $this->foreign('timeId')->title . ':00';
    }

    /**
     *
     */
    public function setSpaceUntil() {
        $this->spaceUntil = date('Y-m-d H:i:s', strtotime($this->spaceSince) + $this->foreign('placeId')->duration * 60);
    }
}