<?php
class Admin_ClientController extends Project_Controller_Admin{
    public function formAction() {
        if (!$this->row->id) parent::formAction();
    }
    public function saveAction(){
        if (!$this->row->id && !Indi::uri()->id) {
            $eventM = Indi::model('Event');


            die('ok');
        }
    }
}