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
}