<?php
class Project_Controller_Admin_Events extends Project_Controller_Admin {

    public function adjustActionCfg() {
        $this->actionCfg['mode']['agreement'] = 'row';
        $this->actionCfg['view']['agreement'] = 'print';
    }

    public function formActionIDate($data) {

        // Check `placeId` param given
        if (!$placeId = $data['placeId']) jflush(false, 'Param "placeId" is not given');

        // Check `placeId` param's value is an integer
        if (!$placeId = Indi::rexm('int11', $placeId, 0))
            jflush(false, sprintf('Value "%s" of param "placeId" is not an integer', $data['placeId']));

        // Check that such `place` entry exists
        if (!$placeR = Indi::model('Place')->fetchRow('`id` = "' . $placeId . '"'))
            jflush(false, sprintf('No `place` entry having "%s" as `id` found', $placeId));

        // Create schedule
        $schedule = Indi::schedule($data['since'], $data['until'])
            ->daily('10:00:00', '20:00:00')
            ->load('event', array('`placeId` = "' . $placeR->id . '"', '`manageStatus` != "036#ff9900"'));

        // Flush disabled dates
        jflush(true, array('disabledDates' => $schedule->busyDates($placeR->duration . 'm')));
    }

    public function formActionITimeId($data) {

        // Check `placeId` param given
        if (!$placeId = $data['placeId']) jflush(false, 'Param "placeId" is not given');

        // Check `placeId` param's value is an integer
        if (!$placeId = Indi::rexm('int11', $placeId, 0))
            jflush(false, sprintf('Value "%s" of param "placeId" is not an integer', $data['placeId']));

        // Check that such `place` entry exists
        if (!$placeR = Indi::model('Place')->fetchRow('`id` = "' . $placeId . '"'))
            jflush(false, sprintf('No `place` entry having "%s" as `id` found', $placeId));

        // Check `date` param given
        if (!$data['date']) jflush(false, 'Param "date" is not given');

        // Check format of `date` param's value
        if (!Indi::rexm('date', $data['date'])) jflush(false, 'Param "date" is not in format "yyyy-mm-dd"');

        // Create schedule
        $schedule = Indi::schedule('week', $data['date'])
            ->daily('10:00:00', '20:00:00')
            ->load('event', array('`placeId` = "' . $placeR->id . '"', '`manageStatus` != "036#ff9900"'));

        // Check that startDate is not busy
        if (in($data['date'], $schedule->busyDates($placeR->duration . 'm'))) jflush(false, 'Выберите другую дату');

        // Get busy hours
        $hourA = $schedule->busyHours($placeR->duration . 'm', $data['date'], '30m');

        // Get disabled ids
        $timeIdA = Indi::model('Time')->fetchAll('FIND_IN_SET(`title`, "' . im($hourA) . '")')->column('id');

        // Flush busy time ids
        jflush(true, array('disabledTimeIds' => $timeIdA));
    }

    public function formActionIAnimatorId($data) {

        // Check `placeId` param given
        if (!$placeId = $data['placeId']) jflush(false, 'Param "placeId" is not given');

        // Check `placeId` param's value is an integer
        if (!$placeId = Indi::rexm('int11', $placeId, 0))
            jflush(false, sprintf('Value "%s" of param "placeId" is not an integer', $data['placeId']));

        // Check that such `place` entry exists
        if (!$placeR = Indi::model('Place')->fetchRow('`id` = "' . $placeId . '"'))
            jflush(false, sprintf('No `place` entry having "%s" as `id` found', $placeId));

        // Check `timeId` param given
        if (!$timeId = $data['timeId']) jflush(false, 'Param "timeId" is not given');

        // Check `timeId` param's value is an integer
        if (!$timeId = Indi::rexm('int11', $timeId, 0))
            jflush(false, sprintf('Value "%s" of param "timeId" is not an integer', $data['timeId']));

        // Check that such `time` entry exists
        if (!$timeR = Indi::model('Time')->fetchRow('`id` = "' . $timeId . '"'))
            jflush(false, sprintf('No `time` entry having "%s" as `id` found', $timeId));

        // Check `date` param given
        if (!$data['date']) jflush(false, 'Param "date" is not given');

        // Check format of `date` param's value
        if (!Indi::rexm('date', $data['date'])) jflush(false, 'Param "date" is not in format "yyyy-mm-dd"');

        // Check `programId` param's value is an integer
        if (!Indi::rexm('int11', $data['programId']))
            jflush(false, sprintf('Value "%s" of param "programId" is not an integer', $data['programId']));

        // Check that such `program` entry exists
        if (!$programR = Indi::model('Program')->fetchRow('`id` = "' . $data['programId'] . '"'))
            jflush(false, sprintf('No `program` entry having "%s" as `id` found', $data['programId']));

        // Check `subprogramId` param's value is an integer
        if ($data['subprogramId'] && !Indi::rexm('int11', $data['subprogramId']))
            jflush(false, sprintf('Value "%s" of param "subprogramId" is not an integer', $data['subprogramId']));

        // Check that such `subprogram` entry exists
        if ($data['subprogramId'] && !$subprogramR = Indi::model('Subprogram')->fetchRow('`id` = "' . $data['subprogramId'] . '"'))
            jflush(false, sprintf('No `subprogram` entry having "%s" as `id` found', $data['subprogramId']));

        // Declare array for disabled animator ids
        $disabledA = array();

        // General WHERE clause
        $where = array(
            '`manageStatus` != "036#ff9900"',
            '`placeId` != "' . $placeR->id . '"'
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
            if ($schedule->busy($data['date'] . ' ' . $timeR->title . ':00', $placeR->duration * 60 + $gap, true)) $disabledA[] = $animatorId;
        }

        // Animators qty
        $aQty = $subprogramR->animatorsCount ?: 1;

        // Get initial price
        $price = $placeR->foreign('districtId')->{'price' . $aQty};

        // If $data['date'] is not a saturday/sunday and is not a holiday other holiday
        if (!in(date('N', strtotime($data['date'])), '6,7')
            && !Indi::model('Holiday')->fetchRow('`title` = "' . $data['date'] . '"')
            && ($discount = Indi::blocks('work-day-discount'))
            && ((!$until = Indi::blocks('discount-until-time')) || $timeR->title < $until)) {

            // Get discounted price
            $price *= (100 - $discount)/100;
        }

        // Flush busy time ids
        jflush(true, array('disabled' => $disabledA, 'price' => $price));
    }
}