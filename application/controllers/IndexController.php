<?php
class IndexController extends Indi_Controller_Front {

    public function formAction() {

        // Fix combo data filtering
        $this->row->field('placeId')->filter = '`id` NOT IN (<?=Indi::admin()->id ? \'5,7\' : \'0\'?>)';

        // Prepare json for timeId values
        $this->row->view('timeId-options', str_replace('"', '&quot;',
            json_encode($this->row->getComboData('timeId')->column('id,title'))));
    }

    public function formActionIDate($data) {

        // Flush disabled dates
        jflush(true, array('disabledDates' => $this->row->busyDates($data)));
    }

    public function formActionITimeId($data) {

        // Convert date format
        if ($data['date']) $data['date'] = date('Y-m-d', strtotime($data['date']));

        // Flush busy time ids
        jflush(true, array('disabledTimeIds' => $this->row->busyTimes($data)));
    }

    public function saveAction() {

        // Convert date format
        if (Indi::post('date')) Indi::post()->date = date('Y-m-d', strtotime(Indi::post('date')));

        // Check params, and show error messages on-by-one, if detected
        $this->row->mcheck(array(
            'placeId' => array('req' => true),
            'date' => array('req' => true, 'rex' => 'date'),
            'timeId' => array('req' => true),
            'clientTitle' => array('req' => true),
            'clientPhone' => array('req' => true),
            'clientEmail' => array('req' => true, 'rex' => 'email'),
        ), Indi::post());

        // Set required fields
        $form = array(
            'required' => 'districtId,placeId,date,timeId,clientTitle,clientPhone,clientEmail',
        );

        // Update modes of some fields
        foreach(Indi::trail()->fields as $fieldR)
            if (in($fieldR->alias, $form['required'])) $fieldR->mode = Indi::post('redirect') ? 'regular' : 'required';
            else if (in($fieldR->alias, $form['regular'])) $fieldR->mode = 'regular'; else {
                $fieldR->mode = 'readonly';
                $this->appendDisabledField($fieldR->alias);
            }

        // Call parent
        $response = parent::saveAction(true);

        // If client's email is valid - send notification
        if (Indi::rexm('email', $email = $this->row->clientEmail)) {
            $mailer = Indi::mailer();
            $mailer->Subject = 'Ваша заявка на проведение детского праздника';
            $mailer->Body = Indi::view()->mailConfirmation($this->row);
            $mailer->addAddress($email);
            $mailer->addBCC('pavel.perminov.23@gmail.com', 'Pavel Perminov');
            $mailer->send();
        }

        // If district's email is valid - send notification
        if (Indi::rexm('email', $email = $this->row->foreign('districtId')->email)) {
            $mailer = Indi::mailer();
            $mailer->Subject = 'Поступила новая заявка на проведение детского праздника';
            $mailer->Body = Indi::view()->mailEvent($this->row);
            $mailer->addAddress($email);
            $mailer->addBCC('pavel.perminov.23@gmail.com', 'Pavel Perminov');
            $mailer->send();
        }

        // Prepare response msg
        $response['msg'] = str_replace('%clientTitle%', $this->row->clientTitle, Indi::blocks('request-saved'));
        $response['msg'] = str_replace('%addr%', $this->row->foreign('districtId')->address, $response['msg']);
        $response['msg'] = str_replace('%date%', $this->row->date('date', 'd.m.Y'), $response['msg']);
        $response['msg'] = str_replace('%time%', $this->row->foreign('timeId')->title, $response['msg']);
        $response['msg'] = str_replace(ar('[,]'), ar('<,>'), $response['msg']);

        // Flush success
        jflush($response);
    }
}