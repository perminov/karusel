<?php
class Event extends Indi_Db_Table{

    protected $_rowClass = 'Event_Row';

    //public function disabledDates($placeId, $animatorsNeededCount, $eventId = null){
    public function disabledDates($placeId, $eventId = null){
        $disabledDates = array();
        /**
         * Первый тип неактивных дат - это даты, а которые полностью забито расписание
         */
        $maxEventsCount = $this->getAdapter()->query('SELECT COUNT(`id`) FROM `time` WHERE `placeId` = "' . $placeId . '"')->fetchColumn(0);
        $dateA = $this->getAdapter()->query('
                SELECT
                  `e`.`date`
                FROM
                  `event` `e`,
                  `time` `t`
                WHERE 1
                  AND `e`.`placeId` = "' . $placeId . '"
                  AND `t`.`id` = `e`.`timeId`
                ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
                GROUP BY
                  `e`.`date`
                HAVING COUNT(`t`.`id`) = ' . $maxEventsCount
        )->fetchAll();

        foreach ($dateA as $dateI) $disabledDates[$dateI['date']] = true;

        /**
         * Второй тип неактивных дат - это даты, в которые вообще нет свободных аниматоров
         */
        /*$animatorsCount = $this->getAdapter()->query('SELECT COUNT(`id`) FROM `animator`')->fetchColumn(0);

        $tmpTableName = 'animatorsAvailabilityForPlace' . $placeId;
        $this->getAdapter()->query($sql = '
                CREATE TEMPORARY TABLE `' . $tmpTableName . '`
                SELECT
                  `e`.`date`,
                  `t`.`id`,
                  `t`.`title` AS `start`,
                  SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) AS `end`,
                  "' . $animatorsCount . '" - COUNT(DISTINCT `ea`.`animatorId`) AS `available`
                FROM
                  `time` `t`,
                  `place` `p`,
                  `event` `e`,
                  `time` `ot`,
                  `place` `op`,
                  `eventAnimator` `ea`
                WHERE 1
                  AND `t`.`placeId` = `p`.`id`
                  AND `p`.`id` = "' . $placeId . '"
                  AND `op`.`id` != `p`.`id`
                  AND `e`.`placeId` = `op`.`id`
                ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
                  AND `e`.`timeId` = `ot`.`id`
                  AND `ot`.`placeId` = `op`.`id`
                  AND `ea`.`eventId` = `e`.`id`
                  AND (
                      (`t`.`title` <= `ot`.`title` AND `ot`.`title` < SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5)) OR
                      (`t`.`title` < SUBSTR(DATE_ADD(TIME(`ot`.`title`), INTERVAL CAST(CONCAT(`op`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) AND SUBSTR(DATE_ADD(TIME(`ot`.`title`), INTERVAL CAST(CONCAT(`op`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) <= SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5))
                  )
                GROUP BY CONCAT(`date`,`t`.`title`)
            ');

        $dateA = $this->getAdapter()->query('
                SELECT `date`, MAX(`available`) AS `available`
                FROM `' . $tmpTableName . '`
                GROUP BY (`date`)
                HAVING `available` < "' . $animatorsNeededCount . '"
            ')->fetchAll();

        foreach ($dateA as $dateI) $disabledDates[$dateI['date']] = true;

        $this->getAdapter()->query('DROP TABLE `' . $tmpTableName . '`');*/
        return array_keys($disabledDates);
    }

    public function disabledTimes($placeId, $date, $eventId = null){
        $disabledTimes = array();

        $timeA = $this->getAdapter()->query('
                SELECT
                  `t`.`id`
                FROM
                  `event` `e`,
                  `time` `t`
                WHERE 1
                  AND `e`.`placeId` = "' . $placeId . '"
                  AND `e`.`date` = "' . $date . '"
                 ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
                 AND `t`.`id` = `e`.`timeId`
            ')->fetchAll();

        foreach ($timeA as $timeI) $disabledTimes[$timeI['id']] = true;

        /*$timeA = $this->getAdapter()->query('
                SELECT
                  `e`.`date`,
                  `t`.`id`,
                  `t`.`title` AS `start`,
                  SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) AS `end`,
                  "4" - COUNT(DISTINCT `ea`.`animatorId`) AS `available`
                FROM
                  `time` `t`,
                  `place` `p`,
                  `event` `e`,
                  `time` `ot`,
                  `place` `op`,
                  `eventAnimator` `ea`
                WHERE 1
                  AND `t`.`placeId` = `p`.`id`
                  AND `p`.`id` = "'. $placeId . '"
                  AND `op`.`id` != `p`.`id`
                  AND `e`.`placeId` = `op`.`id`
                  AND `e`.`timeId` = `ot`.`id`
                  AND `ot`.`placeId` = `op`.`id`
                  AND `ea`.`eventId` = `e`.`id`
                  AND `e`.`date` = "' . $date . '"
                ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
                  AND (
                      (`t`.`title` <= `ot`.`title` AND `ot`.`title` < SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5)) OR
                      (`t`.`title` < SUBSTR(DATE_ADD(TIME(`ot`.`title`), INTERVAL CAST(CONCAT(`op`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) AND SUBSTR(DATE_ADD(TIME(`ot`.`title`), INTERVAL CAST(CONCAT(`op`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) <= SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5))
                  )
                GROUP BY CONCAT(`date`,`t`.`title`)
                HAVING `available` < "'. $animatorsNeededCount . '"
            ')->fetchAll();

        foreach ($timeA as $timeI) $disabledTimes[$timeI['id']] = true;

        $price = $this->getAdapter()->query('
                SELECT
                  CAST((IF("'. $animatorsNeededCount . '" = "1", `price1`, `price2`) -
                  IF("'. $animatorsNeededCount . '" = "1",

                    `price1` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $date . '") NOT IN(5,6), 1, 0) *
                      ((CAST(`s`.`detailsString` AS DECIMAL))/100),

                    `price2` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $date . '") NOT IN(5,6), 1, 0) *
                      ((CAST(`s`.`detailsString` AS DECIMAL))/100)

                  )) AS DECIMAL)
                  AS `price`
                FROM
                  `district` `d`,
                  `place` `p`
                  LEFT JOIN `holiday` `h` ON (`h`.`title` = "' . $date . '")
                  LEFT JOIN `staticblock` `s` ON (`s`.`alias` = "work-day-discount")
                WHERE 1
                  AND `p`.`id` = "' . $placeId . '"
                  AND `d`.`id` = `p`.`districtId`
            ')->fetchColumn(0);
        return array('price' => $price, 'disabledTimeIds' => array_keys($disabledTimes));*/
        return array_keys($disabledTimes);
    }

    public function disabledAnimators($placeId, $date, $timeId, $animatorsNeededCount, $eventId = null){
        $disabledA = $this->getAdapter()->query('
            SELECT
              `e`.`date`,
              `t`.`id` AS `timeId`,
              `t`.`title` AS `start`,
              SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) AS `end`,
              GROUP_CONCAT(DISTINCT `ea`.`animatorId`) AS `disabled`
            FROM
              `time` `t`,
              `place` `p`,
              `event` `e`,
              `time` `ot`,
              `place` `op`,
              `eventAnimator` `ea`
            WHERE 1
              AND `t`.`placeId` = `p`.`id`
              AND `p`.`id` = "' . $placeId . '"
              AND `op`.`id` != `p`.`id`
              AND `e`.`placeId` = `op`.`id`
              AND `e`.`timeId` = `ot`.`id`
              AND `ot`.`placeId` = `op`.`id`
              AND `ea`.`eventId` = `e`.`id`
              AND `e`.`date` = "' . $date . '"
              ' . ($eventId ? ' AND `e`.`id` != "' . $eventId . '" ' : '') . '
              AND `t`.`id` = "' . $timeId . '"
              AND (
                  (`t`.`title` <= `ot`.`title` AND `ot`.`title` < SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5)) OR
                  (`t`.`title` < SUBSTR(DATE_ADD(TIME(`ot`.`title`), INTERVAL CAST(CONCAT(`op`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) AND SUBSTR(DATE_ADD(TIME(`ot`.`title`), INTERVAL CAST(CONCAT(`op`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5) <= SUBSTR(DATE_ADD(TIME(`t`.`title`), INTERVAL CAST(CONCAT(`p`.`duration`) AS UNSIGNED)+30 MINUTE), 1, 5))
              )
            GROUP BY CONCAT(`date`,`t`.`title`)
        ')->fetchColumn(4);

        if (!$animatorsNeededCount) $animatorsNeededCount = 1;
        $price = $this->getAdapter()->query('
                SELECT
                  CAST((IF("'. $animatorsNeededCount . '" = "1", `price1`, `price2`) -
                  IF("'. $animatorsNeededCount . '" = "1",

                    `price1` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $date . '") NOT IN(5,6), 1, 0) *
                      ((CAST(`s`.`detailsString` AS DECIMAL))/100),

                    `price2` *
                      IF(ISNULL(`h`.`title`) AND WEEKDAY("' . $date . '") NOT IN(5,6), 1, 0) *
                      ((CAST(`s`.`detailsString` AS DECIMAL))/100)

                  )) AS DECIMAL)
                  AS `price`
                FROM
                  `district` `d`,
                  `place` `p`
                  LEFT JOIN `holiday` `h` ON (`h`.`title` = "' . $date . '")
                  LEFT JOIN `staticblock` `s` ON (`s`.`alias` = "work-day-discount")
                WHERE 1
                  AND `p`.`id` = "' . $placeId . '"
                  AND `d`.`id` = `p`.`districtId`
            ')->fetchColumn(0);

        return array('disabled' => (strlen($disabledA) ? explode(',', $disabledA) : array()), 'price' => $price);
    }
}