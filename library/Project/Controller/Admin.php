<?php
class Project_Controller_Admin extends Indi_Controller_Admin{
    public function preDispatch(){
        if ($_SESSION['admin']['id'] == 15 && $this->controller != 'client') unset($_SESSION['admin']);
        parent::preDispatch();
    }
}