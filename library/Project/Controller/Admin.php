<?php
class Project_Controller_Admin extends Indi_Controller_Admin{
    public function preDispatch(){
        if ($_SESSION['admin']['id'] == 15 && $this->controller != 'client') unset($_SESSION['admin']);
        parent::preDispatch();
    }
    
    public function setGridTitlesByCustomLogic(&$data){
        for($i = 0; $i < count($data); $i++) {
            foreach ($data[$i] as $k => $v) {
                if ($v == '00.00.0000') $data[$i][$k] = '';
            }
        }
        parent::setGridTitlesByCustomLogic($data);
    }
}