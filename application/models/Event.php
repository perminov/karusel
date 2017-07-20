<?php
class Event extends Indi_Db_Table_Schedule {

    /**
     * Turn changes logging to 'On' for this model
     *
     * @var array
     */
    protected $_changeLog = array(
        'toggle' => true,
        'ignore' => 'price,clientAgreementNumber,title,birthChildAge,requestBy,requestByManagerId,requestDate,calendarStart,calendarEnd,spaceSince,spaceUntil,spaceFrame'
    );

    /**
     * @var string
     */
    protected $_rowClass = 'Event_Row';

    public function disabledDates($placeId, $eventId = null){
        $disabledDates = array();
        $dateA = Indi::db()->query('
            SELECT
              GROUP_CONCAT(
                IF(`e`.`timeId` - 1 > 0, CONCAT(`e`.`timeId` - 1, ",",
                  IF(`e`.`timeId` - 2 > 0, CONCAT(`e`.`timeId` - 2, ",",
                    IF(`e`.`timeId` - 3 > 0, CONCAT(`e`.`timeId` - 3, ","), ",")
                  ), "")
                ), ""),
                `e`.`timeId`,
                IF(`e`.`timeId` + 1 < 18, CONCAT(",", `e`.`timeId` + 1,
                  IF(`e`.`timeId` + 2 < 18, CONCAT(",", `e`.`timeId` + 2,
                    IF(`e`.`timeId` + 3 < 18, CONCAT(",", `e`.`timeId` + 3), "")
                  ), "")
                ), "")
              ) AS `tmp`,
              `e`.`date`
            FROM
              `event` `e`,
              `time` `t`
            WHERE 1
              AND `e`.`timeId` = `t`.`id`
              AND `e`.`placeId` = "' . $placeId . '"
			  AND `e`.`manageStatus` != "036#ff9900"
              ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
            GROUP BY `e`.`date`
            HAVING 1
              AND FIND_IN_SET(1, tmp) AND FIND_IN_SET(2, tmp) AND FIND_IN_SET(3, tmp) AND FIND_IN_SET(4, tmp) AND FIND_IN_SET(5, tmp)
              AND FIND_IN_SET(6, tmp) AND FIND_IN_SET(7, tmp) AND FIND_IN_SET(8, tmp) AND FIND_IN_SET(9, tmp) AND FIND_IN_SET(10, tmp)
              AND FIND_IN_SET(11, tmp) AND FIND_IN_SET(12, tmp) AND FIND_IN_SET(13, tmp) AND FIND_IN_SET(14, tmp) AND FIND_IN_SET(15, tmp)
              AND FIND_IN_SET(16, tmp) AND FIND_IN_SET(17, tmp)
        ')->fetchAll();

        foreach ($dateA as $dateI) $disabledDates[$dateI['date']] = true;

        $disabledDates = array_keys($disabledDates);
        for($i = 0; $i < count($disabledDates); $i++) {
            $disabledDates[$i] = date('d.m.Y', strtotime($disabledDates[$i]));
        }
        
        return $disabledDates;
    }

    public function disabledTimes($placeId, $date, $eventId = null){
        $disabledTimes = array();
        $timeA = Indi::db()->query('
            SELECT
              GROUP_CONCAT(
                IF(`e`.`timeId` - 1 > 0, CONCAT(`e`.`timeId` - 1, ",",
                  IF(`e`.`timeId` - 2 > 0, CONCAT(`e`.`timeId` - 2, ",",
                    IF(`e`.`timeId` - 3 > 0, CONCAT(`e`.`timeId` - 3, ","), ",")
                  ), "")
                ), ""),
                `e`.`timeId`,
                IF(`e`.`timeId` + 1 < 18, CONCAT(",", `e`.`timeId` + 1,
                  IF(`e`.`timeId` + 2 < 18, CONCAT(",", `e`.`timeId` + 2,
                    IF(`e`.`timeId` + 3 < 18, CONCAT(",", `e`.`timeId` + 3), "")
                  ), "")
                ), "")
              ) AS `tmp`,
              `e`.`date`
            FROM
              `event` `e`,
              `time` `t`
            WHERE 1
              AND `e`.`timeId` = `t`.`id`
              AND `e`.`placeId` = "' . $placeId . '"
			  AND `e`.`manageStatus` != "036#ff9900"
              ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
              AND `e`.`date` = "' . $date . '"
            GROUP BY `e`.`date`
        ')->fetchColumn(0);
        $timeA = array_unique(explode(',', $timeA));
        foreach ($timeA as $timeI) $disabledTimes[] = (int) $timeI;
        return $disabledTimes;
    }
}