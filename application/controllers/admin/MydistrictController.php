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
}