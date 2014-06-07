<?php
class Admin_ClientController extends Project_Controller_Admin{
    public function formAction() {
        if (!$this->row->id) parent::formAction();
    }
    public function saveAction(){
        if (!$this->row->id && !Indi::uri()->id) {
            $eventM = Indi::model('Event');

            $disabledDates = $eventM->disabledDates(Indi::post('placeId'));
            if (in_array(Indi::post('date'), $disabledDates)) die('expiredDate');

            $disabledTimes = $eventM->disabledTimes(Indi::post('placeId'), Indi::post('date'));
            if (in_array(Indi::post('timeId'), $disabledTimes)) die('expiredTime');

            $disabledAnimators = $eventM->disabledAnimators(Indi::post('placeId'), Indi::post('date'), Indi::post('timeId'), Indi::post('animatorsCount'));
            $animatorsCount = Indi::db()->query('SELECT COUNT(`id`) FROM `animator`')->fetchColumn(0);
            if (Indi::post('animatorsCount') > $animatorsCount - count($disabledAnimators['disabled'])) die('expiredAnimators');

            parent::saveAction(false);

            if ($_SESSION['admin']['id'] == '15') unset($_SESSION);
            
            if (!$this->row->id) die(json_encode(array('error' => $this->row->mismatch())));

            $eventR = $this->row; //Indi::trail()->model->fetchRow('`id` = "' . $this->identifier . '"');
            
            // Добавляем путь к хелперам уведомлений
            $coreH = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . STD . '/core/library';
            $wwwH  = preg_replace('/core(\/library)/', 'www$1', $coreH);
            if (is_dir($wwwH)) $this->view->addHelperPath($wwwH . '/Project/View/Helper', 'Project_View_Helper_');
            
            // Уведомление заказчику
            if (preg_match(Indi::rex('email'), $eventR->clientEmail)) {
                $subject = 'Ваша заявка на проведение детского праздника';
                $message = $this->view->mailConfirmation($eventR);
                $emails = array(); $emails[] = $eventR->clientEmail;
                @mail(implode(',', $emails), iconv('UTF-8','KOI8-R', $subject), iconv('UTF-8','KOI8-R', $message), iconv('UTF-8','KOI8-R', 'From: Кенгуру <info@' . $_SERVER['HTTP_HOST'] . '>' . "\r\n" . 'Content-Type: text/html; charset=koi8-r' . "\r\n" . 'Content-Length: ' . strlen($message)));
            }

            // Уведомление для локейшена
            $districtR = $eventR->foreign('districtId');
            if (preg_match(Indi::rex('email'), $districtR->email)) {
                $subject = 'Поступила новая заявка на проведение детского праздника';
                $message = $this->view->mailEvent($eventR);
                $emails = array(); $emails[] = $districtR->email;
                @mail(implode(',', $emails), iconv('UTF-8','KOI8-R', $subject), iconv('UTF-8','KOI8-R', $message), iconv('UTF-8','KOI8-R', 'From: Кенгуру <info@' . $_SERVER['HTTP_HOST'] . '>' . "\r\n" . 'Content-Type: text/html; charset=koi8-r' . "\r\n" . 'Content-Length: ' . strlen($message)));
            }

            die('ok');
        }
    }
}