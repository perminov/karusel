<?php
class Admin_MydistrictController extends Project_Controller_Admin_Calendar{
    public function confirmAction(){
        if ($this->row->manageStatus == 'confirmed') {
            $response = 'already';
        } else if ($this->post['managePrepay']){
            $this->row->managePrepay = $this->post['managePrepay'];
            $this->row->manageManagerId = $_SESSION['admin']['id'];
            $this->row->manageStatus = 'confirmed';
            $this->row->manageDate = date('Y-m-d');
            $this->row->save();
            $response = 'Заявка отмечена как подтвержденная';
        } else {
            $response = '<span id="msgbox-prepay"></span>';
        }
        die($response);
    }

    public function agreementAction(){
        if ($this->params['check'] && $this->row->manageStatus != 'confirmed') {
            die('not-confirmed');
        }
    }
}