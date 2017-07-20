<?php
class Project_Controller_Admin_Events extends Project_Controller_Admin {

    public function adjustActionCfg() {
        $this->actionCfg['mode']['agreement'] = 'row';
        $this->actionCfg['view']['agreement'] = 'print';
    }

    public function formActionIDate($data) {

        // Check $data arg
        $rowA = jcheck(array(
            'placeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Place'
            ),
            'since,until' => array(
                'req' => true,
                'rex' => 'date'
            )
        ), $data);

        // Create schedule
        $schedule = Indi::schedule($data['since'], $data['until'])
            ->daily('10:00:00', '20:00:00')
            ->load('event', array(
                '`placeId` = "' . $data['placeId'] . '"',
                '`id` != "' . $this->row->id . '"',
                '`manageStatus` != "036#ff9900"'
            ));

        // Flush disabled dates
        jflush(true, array('disabledDates' => $schedule->busyDates($rowA['placeId']->duration . 'm')));
    }

    public function formActionITimeId($data) {

        // Check $data arg
        $rowA = jcheck(array(
            'placeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Place'
            ),
            'date' => array(
                'req' => true,
                'rex' => 'date'
            )
        ), $data);

        // Create schedule
        $schedule = Indi::schedule('week', $data['date'])
            ->daily('10:00:00', '20:00:00')
            ->load('event', array(
                '`placeId` = "' . $data['placeId'] . '"',
                '`id` != "' . $this->row->id . '"',
                '`manageStatus` != "036#ff9900"'
            ));

        // Check that startDate is not busy
        if (in($data['date'], $schedule->busyDates($rowA['placeId']->duration . 'm'))) jflush(false, 'Выберите другую дату');

        // Get busy hours
        $hourA = $schedule->busyHours($rowA['placeId']->duration . 'm', $data['date'], '30m');

        // Get disabled ids
        $timeIdA = Indi::model('Time')->fetchAll('FIND_IN_SET(`title`, "' . im($hourA) . '")')->column('id');

        // Flush busy time ids
        jflush(true, array('disabledTimeIds' => $timeIdA));
    }

    public function formActionIAnimatorId($data) {

        // Check $data arg
        $rowA = jcheck(array(
            'placeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Place'
            ),
            'timeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Time'
            ),
            'date' => array(
                'req' => true,
                'rex' => 'date'
            ),
            'programId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Program'
            ),
            'subprogramId' => array(
                'req' => false,
                'rex' => 'int11',
                'key' => 'Subprogram'
            )
        ), $data);

        // Declare array for disabled animator ids
        $disabledA = array();

        // General WHERE clause
        $where = array(
            '`manageStatus` != "036#ff9900"',
            '`placeId` != "' . $data['placeId'] . '"',
            '`id` != "' . $this->row->id . '"',
        );

        // Gap, in seconds
        $gap = 1800;

        // Get animators, involved in events at date, specified by $data['date']
        $animatorIdA = array_unique(ar(Indi::model('Event')
            ->fetchAll(array_merge($where, array('`date` = "' . $data['date'] . '"', '`animatorId` != ""')))
            ->column('animatorId', true, true)));

        // Foreach animator, involved in events at date, specified by $data['date']
        foreach ($animatorIdA as $animatorId) {

            // Create schedule
            $schedule = Indi::schedule('week', $data['date'])

                // Set daily active hours
                ->daily('10:00:00', '20:30:00')

                // Load animator's events
                ->load('event', array_merge(array('FIND_IN_SET("' . $animatorId . '", `animatorId`)'), $where), function(&$r, $sp) {
                    $r->{$sp['frame']} += ($gap = 1800);
                });

            // If animator is busy - push it's id to $disabled array
            if ($schedule->busy($data['date'] . ' ' . $rowA['timeId']->title . ':00', $rowA['placeId']->duration * 60 + $gap, true)) $disabledA[] = $animatorId;
        }

        // Animators qty
        $aQty = $rowA['subprogramId']->animatorsCount ?: 1;

        // Get initial price
        $price = $rowA['placeId']->foreign('districtId')->{'price' . $aQty};

        // If $data['date'] is not a saturday/sunday and is not a holiday other holiday
        if (!in(date('N', strtotime($data['date'])), '6,7')
            && !Indi::model('Holiday')->fetchRow('`title` = "' . $data['date'] . '"')
            && ($discount = Indi::blocks('work-day-discount'))
            && ((!$until = Indi::blocks('discount-until-time')) || $rowA['timeId']->title < $until)) {

            // Get discounted price
            $price *= 1 - $discount/100;
        }

        // Flush busy time ids
        jflush(true, array('disabled' => $disabledA, 'price' => $price));
    }

    public function confirmAction(){
        if ($this->row->manageStatus != '240#0000ff') {
            $response = 'already';
        } else if (Indi::post('managePrepay')){
            $this->row->managePrepay = Indi::post('managePrepay');
            $this->row->manageManagerId = Indi::post('manageManagerId') ?: $_SESSION['admin']['id'];
            $this->row->manageStatus = '#00ff00';
            $this->row->manageDate = date('Y-m-d');
            $this->row->save();
            $this->row->setAgreementNumber();
            $response = 'Заявка отмечена как подтвержденная';
        } else {
            $managerRs = Indi::model('Manager')->fetchAll();
            $options = array(); foreach($managerRs as $managerR) $options[] = '<option value="' . $managerR->id . '"' . ($managerR->id == $_SESSION['admin']['id'] ? ' selected="selected"' : '') .'>' . $managerR->title . '</option>';
            $response = '<span id="msgbox-prepay"></span><select id="manageManagerId">' . implode('', $options) . '</select><br/><br/><br/>';
        }
        die($response);
    }

    public function cancelAction(){
        if ($this->row->manageStatus != '120#00ff00') {
            $response = 'forbidden';
        } else {
            $this->row->manageStatus = '#ff9900';
            $this->row->save();
            $response = 'ok';
        }
        die($response);
    }

    public function agreementAction(){
        if (Indi::uri()->check && $this->row->manageStatus == '240#0000ff') {
            die('not-confirmed');
        }
        Indi::trail()->view->mode = 'view';
        //if (Indi::uri()->checkConfirmed) die($this->row->manageStatus != '120#00ff00' ? 'not-confirmed': 'ok');
    }
}