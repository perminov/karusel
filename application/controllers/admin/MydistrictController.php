<?php
class Admin_MydistrictController extends Project_Controller_Admin_Calendar{
    public function confirmAction(){
        if ($this->row->manageStatus == '120#00ff00') {
            $response = 'already';
        } else if ($this->post['managePrepay']){
            $this->row->managePrepay = $this->post['managePrepay'];
            $this->row->manageManagerId = $_SESSION['admin']['id'];
            $this->row->manageStatus = '#00ff00';
            $this->row->manageDate = date('Y-m-d');
            $this->row->save();
            $this->row->setAgreementNumber();
            $response = 'Заявка отмечена как подтвержденная';
        } else {
            $response = '<span id="msgbox-prepay"></span>';
        }
        die($response);
    }

    public function agreementAction(){
        if ($this->params['check'] && $this->row->manageStatus != '120#00ff00') {
            die('not-confirmed');
        }
    }
    public function setGridTitlesByCustomLogic(&$data) {
        if (preg_match('/placeId/', $this->get['search'])) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['start'] = $data[$i]['calendarStart'];
                $data[$i]['end'] = $data[$i]['calendarEnd'];
                $data[$i]['cid'] = preg_match('/Подтвержденная/', $data[$i]['manageStatus']) ? 2 : 1;
                list($last, $client) = explode(' ', $data[$i]['clientTitle']);
                $title = $client . ' ' . $data[$i]['clientPhone'] . ' ';
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
                $title .= $data[$i]['details'];
                $data[$i]['title'] = $title;
            }
        } else {
            parent::setGridTitlesByCustomLogic($data);
        }
    }
}