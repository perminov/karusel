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

    public function disabledAnimators($placeId, $date, $timeId, $animatorsNeededCount, $eventId = null){
        $disabledA = Indi::db()->query('
            SELECT
              `e`.`date`,
              `t`.`id` AS `timeId`,
              `t`.`title` AS `start`,
              SUBSTR(TIME(DATE_ADD(CONCAT(`e`.`date`, " ", `t`.`title`, ":00"), INTERVAL 150 MINUTE)), 1, 5) AS `end`,              
              GROUP_CONCAT(DISTINCT `ea`.`animatorId`) AS `disabled`
            FROM
              `time` `t`,
              `event` `e`,
              `time` `ot`,
              `eventAnimator` `ea`
            WHERE 1
              AND `t`.`id` = "' . $timeId  .'"
              AND `e`.`date` = "' . $date . '"
              AND `e`.`placeId` != "' . $placeId . '"
			  AND `e`.`manageStatus` != "036#ff9900"
              ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
              AND `e`.`timeId` = `ot`.`id`
              AND `ea`.`eventId` = `e`.`id`
              AND (
                  (`t`.`title` <= `ot`.`title` AND `ot`.`title` < SUBSTR(TIME(DATE_ADD(CONCAT(`e`.`date`, " ", `t`.`title`, ":00"), INTERVAL 150 MINUTE)), 1, 5)) OR
                  (`t`.`title` < SUBSTR(TIME(DATE_ADD(CONCAT(`e`.`date`, " ", `ot`.`title`, ":00"), INTERVAL 150 MINUTE)), 1, 5) AND SUBSTR(TIME(DATE_ADD(CONCAT(`e`.`date`, " ", `ot`.`title`, ":00"), INTERVAL 150 MINUTE)), 1, 5) <= SUBSTR(TIME(DATE_ADD(CONCAT(`e`.`date`, " ", `t`.`title`, ":00"), INTERVAL 150 MINUTE)), 1, 5))
              )
            GROUP BY CONCAT(`date`,`t`.`title`)
        ')->fetchColumn(4);

        if (!$animatorsNeededCount) $animatorsNeededCount = 1;
        $price = Indi::db()->query('
                SELECT
                  CAST((IF("'. $animatorsNeededCount . '" = "1", `price1`, `price2`) -
                  IF("'. $animatorsNeededCount . '" = "1",

                    `price1` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $date . '") NOT IN(5,6) AND IF(ISNULL(`s`.`detailsString`), 0, 1) AND IF(ISNULL(`s2`.`detailsString`), 1, `t`.`title` < `s2`.`detailsString`), 1, 0) *
                      (IF(ISNULL(`s`.`detailsString`), 0, CAST(`s`.`detailsString` AS DECIMAL))/100),

                    `price2` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $date . '") NOT IN(5,6) AND IF(ISNULL(`s`.`detailsString`), 0, 1) AND IF(ISNULL(`s2`.`detailsString`), 1, `t`.`title` < `s2`.`detailsString`), 1, 0) *
                      (IF(ISNULL(`s`.`detailsString`), 0, CAST(`s`.`detailsString` AS DECIMAL))/100)

                  )) AS DECIMAL)
                  AS `price`
                FROM
                  `district` `d`,
                  `place` `p`,
                  `time` `t`
                  LEFT JOIN `holiday` `h` ON (`h`.`title` = "' . $date . '")
                  LEFT JOIN `staticblock` `s` ON (`s`.`alias` = "work-day-discount" AND `s`.`toggle` = "y")
                  LEFT JOIN `staticblock` `s2` ON (`s2`.`alias` = "discount-until-time" AND `s2`.`toggle` = "y")
                WHERE 1
                  AND `p`.`id` = "' . $placeId . '"
                  AND `d`.`id` = `p`.`districtId`
                  AND `t`.`id` = "' . $timeId . '"
            ')->fetchColumn(0);

        return array('disabled' => (strlen($disabledA) ? explode(',', $disabledA) : array()), 'price' => $price);
    }
}