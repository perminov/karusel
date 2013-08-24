<?php
class Admin_ClientController extends Project_Controller_Admin{
    public function formAction() {
        if (!$this->row->id) parent::formAction();
    }
    public function saveAction(){
        if (!$this->row->id && !$this->params['id']) {
            $eventM = Misc::loadModel('Event');

            $disabledDates = $eventM->disabledDates($this->post['placeId']);
            if (in_array($this->post['date'], $disabledDates)) die('expiredDate');

            $disabledTimes = $eventM->disabledTimes($this->post['placeId'], $this->post['date']);
            if (in_array($this->post['timeId'], $disabledTimes)) die('expiredTime');

            $disabledAnimators = $eventM->disabledAnimators($this->post['placeId'], $this->post['date'], $this->post['timeId'], $this->post['animatorsCount']);
            $animatorsCount = $this->db->query('SELECT COUNT(`id`) FROM `animator`')->fetchColumn(0);
            if ($this->post['animatorsCount'] > $animatorsCount - count($disabledAnimators['disabled'])) die('expiredAnimators');

            parent::saveAction(false);

            if ($_SESSION['adminId'] == '15') unset($_SESSION);
            die('ok');
        }
    }
}