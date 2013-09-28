<?php
class IndexController extends Indi_Controller_Front{
    public function indexAction(){
        if (isset($this->get['notification'])) {
            $eventR = Misc::loadModel('Event')->fetchRow('`id` = "20"');
            
            $eventR->clientEmail = 'pavel.perminov.23@gmail.com';
            // Уведомление заказчику
            if (preg_match($this->emailPattern, $eventR->clientEmail)) {
                $subject = 'Ваша заявка на проведение детского праздника';
                $message = $this->view->mailConfirmation($eventR);
                $emails = array(); $emails[] = $eventR->clientEmail;
                mail(implode(',', $emails), iconv('UTF-8','KOI8-R', $subject), iconv('UTF-8','KOI8-R', $message), iconv('UTF-8','KOI8-R', 'From: Кенгуру <info@' . $_SERVER['HTTP_HOST'] . '>' . "\r\n" . 'Content-Type: text/html; charset=koi8-r' . "\r\n" . 'Content-Length: ' . strlen($message)));
            }

            // Уведомление для локейшена
            $districtR = $eventR->getForeignRowByForeignKey('districtId');
            if (preg_match($this->emailPattern, $districtR->email)) {
                $subject = 'Поступила новая заявка на проведение детского праздника';
                $message = $this->view->mailEvent($eventR);
                $emails = array(); $emails[] = $districtR->email;
                mail(implode(',', $emails), iconv('UTF-8','KOI8-R', $subject), iconv('UTF-8','KOI8-R', $message), iconv('UTF-8','KOI8-R', 'From: Кенгуру <info@' . $_SERVER['HTTP_HOST'] . '>' . "\r\n" . 'Content-Type: text/html; charset=koi8-r' . "\r\n" . 'Content-Length: ' . strlen($message)));
            }
            die();
        }
    }
}