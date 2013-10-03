<?php
class Project_Controller_Admin_EventsGrid extends Project_Controller_Admin{
    public function setGridTitlesByCustomLogic(&$data){
        for ($i = 0; $i < count($data); $i++) {
            preg_match('/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/', $data[$i]['title'], $date);
            $data[$i]['title'] = str_replace($date[1], date('d.m.Y', strtotime($date[1])), $data[$i]['title']);
        }
    }
}