<?php
class Admin_CalendarController extends Project_Controller_Admin_Calendar{
    public function setGridTitlesByCustomLogic(&$data) {
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['start'] = $data[$i]['calendarStart'];
            $data[$i]['end'] = $data[$i]['calendarEnd'];
            $data[$i]['cid'] = preg_match('/Подтвержденная/', $data[$i]['manageStatus']) ? 2 : 1;
            preg_match('/([А-Я]{2}: )/u', $data[$i]['title'], $m);
            $data[$i]['title'] = $m[1] . $data[$i]['placeId'];
        }
    }
}