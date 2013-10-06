<?php
class Admin_IndexController extends Project_Controller_Admin{
    public function homeAction() {
        $this->view->home = Misc::loadModel('Profile')->fetchRow('`id` = "' . $_SESSION['admin']['profileId'] . '"')->home;
    }
    public function postDispatch(){
        $out = parent::postDispatch(true);
        if ($out) die($out);
        die($this->view->render('/index/home.php'));
    }

}