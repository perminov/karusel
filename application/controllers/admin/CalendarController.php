<?php
//class Admin_CalendarController1 extends Project_Controller_Admin_Calendar{
class Admin_CalendarController extends Indi_Controller_Admin{
    public function adjustActionCfg() {
        $this->actionCfg['view']['index'] = 'calendar';
    }
    /*public function adjustGridData(&$data) {
        if (preg_match('/placeId/', Indi::get('search')) || Indi::get('start') == Indi::get('end')) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['start'] = $data[$i]['calendarStart'];
                $data[$i]['end'] = $data[$i]['calendarEnd'];
                $data[$i]['cid'] = $this->setColor($data[$i]);
                list($last, $client) = explode(' ', $data[$i]['clientTitle']);
                $title = $this->setExclaim($data[$i]) . $client . ' ' . $data[$i]['clientPhone'] . ' ';
                list($manager) = explode(' ', $data[$i]['manageManagerId']);
                $title .= '<span style="word-break: normal;">' . $data[$i]['childrenCount'] . '/' . $data[$i]['childrenAge'] . '</span>; ';
                if ($manager) {
                    $title .= '<span style="word-break: normal;">' . $data[$i]['clientAgreementNumber'] .'</span>' . ' - ' . $manager . '; ';
                }
                if ($data[$i]['animatorIds']) {
                    $title .= ($data[$i]['subprogramId'] ? $data[$i]['subprogramId'] : $data[$i]['programId']) . ' ';
                    $animators = explode(', ', $data[$i]['animatorIds']);
                    $lastA = array();
                    foreach ($animators as $animator) {
                        list($lastI) = explode(' ', $animator);
                        $lastA[] = $lastI;
                    }
                    $title .= '[' . implode(', ', $lastA) . '] ';
                } else {
                    $title .= '<span style="color: #cc0000;">';
                    $title .= ($data[$i]['subprogramId'] ? $data[$i]['subprogramId'] : $data[$i]['programId']) . ' ';
                    $title .= '</span> ';
                }
                ;
                $title .= $data[$i]['details'];
                
                if ($data[$i]['manageNotes']) {
                $title .= '<span style="color: #8000A3;">';
                $title .= $data[$i]['manageNotes'];
                $title .= '</span> ';
                }
                
                $data[$i]['title'] = $title;
            }
        } else {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['start'] = $data[$i]['calendarStart'];
                $data[$i]['end'] = $data[$i]['calendarEnd'];
                $data[$i]['cid'] = $this->setColor($data[$i]);
                preg_match('/([А-Я]{2}: )/u', $data[$i]['title'], $m);
                $data[$i]['title'] = $this->setExclaim($data[$i]) . $m[1] . $data[$i]['placeId'];
            }
        }
    }*/
}