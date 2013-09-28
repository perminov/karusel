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

            if ($_SESSION['admin']['id'] == '15') unset($_SESSION);
            
            $eventR = $this->trail->getItem()->model->fetchRow('`id` = "' . $this->identifier . '"');
            
            // Добавляем путь к хелперам уведомлений
            $coreH = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . $_SERVER['STD'] . '/core/library';
            $wwwH  = preg_replace('/core(\/library)/', 'www$1', $coreH);
            if (is_dir($wwwH)) $this->view->addHelperPath($wwwH . '/Project/View/Helper', 'Project_View_Helper_');
            
            // Уведомление заказчику
            if (preg_match($this->emailPattern, $eventR->clientEmail)) {
                $subject = 'Ваша заявка на проведение детского праздника';
                $message = $this->view->mailConfirmation($eventR);
                $emails = array(); $emails[] = $eventR->clientEmail;
                @mail(implode(',', $emails), iconv('UTF-8','KOI8-R', $subject), iconv('UTF-8','KOI8-R', $message), iconv('UTF-8','KOI8-R', 'From: Кенгуру <info@' . $_SERVER['HTTP_HOST'] . '>' . "\r\n" . 'Content-Type: text/html; charset=koi8-r' . "\r\n" . 'Content-Length: ' . strlen($message)));
            }

            // Уведомление для локейшена
            $districtR = $eventR->getForeignRowByForeignKey('districtId');
            if (preg_match($this->emailPattern, $districtR->email)) {
                $subject = 'Поступила новая заявка на проведение детского праздника';
                $message = $this->view->mailEvent($eventR);
                $emails = array(); $emails[] = $districtR->email;
                @mail(implode(',', $emails), iconv('UTF-8','KOI8-R', $subject), iconv('UTF-8','KOI8-R', $message), iconv('UTF-8','KOI8-R', 'From: Кенгуру <info@' . $_SERVER['HTTP_HOST'] . '>' . "\r\n" . 'Content-Type: text/html; charset=koi8-r' . "\r\n" . 'Content-Length: ' . strlen($message)));
            }

            die('ok');
        }
    }
}