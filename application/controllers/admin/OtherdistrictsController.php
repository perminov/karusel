<?php
class Admin_OtherdistrictsController extends Project_Controller_Admin_Calendar{
    public function formAction(){
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
    }
}