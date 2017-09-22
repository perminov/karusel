<?php
class WeekController extends Indi_Controller_Front{
    public function indexAction(){
        $thisWeekEvents = Indi::db()->query('
            SELECT
                `birthChildName`,
                `birthChildAge`
            FROM
                `event`
            WHERE 1
                AND `date` BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7-(WEEKDAY(CURDATE())+1) DAY)
                AND LENGTH(`birthChildName`) > 0
            ORDER BY `date`;
        ')->fetchAll();
        
        for ($i = 0; $i < $thisWeekEvents[$i]; $i++) {
            $thisWeekEvents[$i]['birthChildAge'] = tbq($thisWeekEvents[$i]['birthChildAge'], 'лет,год,года');
            $thisWeekEvents[$i]['birthChildName'] = json_decode($thisWeekEvents[$i]['birthChildName'])->{Indi::ini('lang')->front};
        }

        die(json_encode($thisWeekEvents));
    }
}