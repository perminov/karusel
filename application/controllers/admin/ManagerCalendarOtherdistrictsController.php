<?php
class Admin_ManagerCalendarOtherdistrictsController extends Project_Controller_Admin_Calendar{
    /*public function formAction(){
        if ($this->row->districtId && $this->row->districtId != $_SESSION['admin']['districtId']) {
            if ($this->row->requestBy == 'client' || ($this->row->requestByManagerId && $this->row->requestByManagerId != $_SESSION['admin']['id'])) {
                $this->trail->items[1]->actions->exclude(3);
            }
        }
        parent::formAction();
    }

    public function saveAction() {
        if ($this->row->districtId && $this->row->districtId != $_SESSION['admin']['districtId']) {
            if ($this->row->requestBy == 'client' || ($this->row->requestByManagerId && $this->row->requestByManagerId != $_SESSION['admin']['id'])) {
                $this->redirectToIndex();
            }
        } else parent::saveAction();
    }*/

    public function agreementAction(){
        if ($this->params['check'] && $this->row->manageStatus != '120#00ff00') {
            die('not-confirmed');
        }
    }    
    
    public function confirmAction(){
        if ($this->row->manageStatus == '120#00ff00') {
            $response = 'already';
        } else if ($this->post['managePrepay']){
            $this->row->managePrepay = $this->post['managePrepay'];
            $this->row->manageManagerId = $this->post['manageManagerId'] ? $this->post['manageManagerId'] : $_SESSION['admin']['id'];
            $this->row->manageStatus = '#00ff00';
            $this->row->manageDate = date('Y-m-d');
            $this->row->save();
            $this->row->setAgreementNumber();
            $response = '������ �������� ��� ��������������';
        }
        die($response);
    }
}