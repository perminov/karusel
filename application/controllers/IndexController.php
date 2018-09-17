<?php
class IndexController extends Indi_Controller_Front {

    /**
     * Apply special clauses for combo data filtering
     */
    public function formAction() {

        // If current visitor is logged as cms-user - return
        if (Indi::admin()) return;

        // Else hide 'Rent' options
        $this->row->field('placeId')->filter = 'NOT FIND_IN_SET(`id`, "5,7")';
    }

    /**
     * Setup combo-data fitration for timeId field, so options, explicitly
     * mentioned within place's publicTimeIds-prop - will be displayed only
     */
    public function formActionOdataTimeId() {

        // Check satellite value, assign it as a value of `placeId` prop and pull foreign data
        $this->row->mcheck(array(
            'placeId' => array(
                'rex' => 'int11'
            )
        ), array('placeId' => Indi::post('satellite')));

        // If `publicTimeIds` defined - setup timeId-field's combo data filtration
        if ($timeIds = $this->row->foreign('placeId')->publicTimeIds)
            $this->row->field('timeId')->filter = 'FIND_IN_SET(`id`, "' . $timeIds . '")';
    }

    /**
     * Collect and flush info about inaccessible values, to prevent them from being selected
     *
     * @param $data
     */
    public function formActionIDuration($data) {

        // Convert date format
        if ($data['date'] && !Indi::rexm('date', $data['date']))
            $data['date'] = date('Y-m-d', strtotime($data['date']));

        // Check satellite value, assign it as a value of `placeId` prop and pull foreign data
        $this->row->mcheck(array(
            'placeId' => array('rex' => 'int11'),
            'date' => array('rex' => 'date')
        ), $data);

        // If `publicTimeIds` defined - setup timeId-field's combo data filtration
        if ($timeIds = $this->row->foreign('placeId')->publicTimeIds)
            $this->row->field('timeId')->filter = 'FIND_IN_SET(`id`, "' . $timeIds . '")';

        // Call parent
        $this->callParent();
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
            $mailer->Subject = I_EVENT_MAIL_SUBJ_CLIENT;
            $mailer->Body = Indi::view()->mailConfirmation($this->row);
            $mailer->addAddress($email);
            $mailer->addBCC('pavel.perminov.23@gmail.com', 'Pavel Perminov');
            $mailer->send();
        }

        // If district's email is valid - send notification
        if (Indi::rexm('email', $email = $this->row->foreign('districtId')->email)) {
            $mailer = Indi::mailer();
            $mailer->Subject = I_EVENT_MAIL_SUBJ_ADMIN;
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