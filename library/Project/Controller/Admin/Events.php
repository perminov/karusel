<?php
class Project_Controller_Admin_Events extends Project_Controller_Admin {
    public function adjustActionCfg() {
        $this->actionCfg['mode']['agreement'] = 'row';
        $this->actionCfg['view']['agreement'] = 'print';
    }
}