<?php
class Event_Row extends Indi_Db_Table_Row_Schedule {

    public function validate() {

        // Create schedule
        $schedule = Indi::schedule('month', $this->date)
            ->daily('10:00:00', '20:00:00')
            ->load('event', array(
                '`placeId` = "' . $this->placeId . '"',
                '`id` != "' . $this->id . '"',
                '`manageStatus` != "036#ff9900"'
            ));

        // Get desired space frame
        $frame = $this->foreign('placeId')->duration . 'm';

        // Get busy dates
        $busyDateA = $schedule->busyDates($frame);

        // If current `date` is a totally busy date - set mismatch message
        if (in($this->date, $busyDateA)) $this->mflush('date', 'Эта дата полностью занята');

        // Get busy hours
        $hourA = $schedule->busyHours($frame, $this->date, '30m');

        // Get disabled ids
        $busyTimeIdA = Indi::model('Time')->fetchAll('FIND_IN_SET(`title`, "' . im($hourA) . '")')->column('id');

        // If current `timeId` is a totally busy time - set mismatch message
        if (in($this->timeId, $busyTimeIdA)) $this->mflush('time', 'Это время занято');

        // General WHERE clause
        $where = array(
            '`manageStatus` != "036#ff9900"',
            '`placeId` != "' . $this->placeId . '"',
            '`id` != "' . $this->id . '"',
        );

        // Gap, in seconds
        $gap = 1800;

        // If `animatorId` prop not yet set
        // Foreach animator, involved in events at date, specified by $data['date']
        $busyAnimatorA = array(); if ($this->animatorId) foreach (ar($this->animatorId) as $animatorId) {

            // Create schedule, set daily active hours and load animator's events
            $schedule = Indi::schedule('week', $this->date)
                ->daily('10:00:00', '20:30:00')
                ->load('event', array_merge(array('FIND_IN_SET("' . $animatorId . '", `animatorId`)'), $where), function(&$r, $sp) {
                    $r->{$sp['frame']} += ($gap = 1800);
                });

            // If animator is busy - push it's id to $busyAnimatorA array
            if ($schedule->busy(
                $this->date . ' ' . $this->foreign('timeId')->title . ':00',
                $this->foreign('placeId')->duration * 60 + $gap,
                true
            )) $busyAnimatorA[] = $animatorId;
        }

        // If one or more of selected animators are busy - flush error message
        if (count($busyAnimatorA)) $this->mflush('animatorId', 'Уже занятые аниматоры: '
            . $this->foreign('animatorId')->select($busyAnimatorA)->column('title', ', '));

        $this->mflush('asd', 'asd');
    }

    /**
     * @return int|mixed
     */
    public function save() {

        // Set `title`
        $this->title = sprintf('[%s, %s] %s: %s', $this->date, $this->foreign('timeId')->title,
            $this->foreign('districtId')->code, $this->foreign('placeId')->title);

        // Set child age
        if ($this->isModified('birthChildBirthDate')) $this->birthChildAge
            = $this->zero('birthChildBirthDate') ? 0 : date('Y') - $this->date('birthChildBirthDate', 'Y');

        // Animators qty
        $aQty = $this->foreign('subprogramId')->animatorsCount ?: 1;

        // Get initial price
        $this->price = $this->foreign('placeId')->foreign('districtId')->{'price' . $aQty};

        // If event date is not a saturday/sunday and is not an other holiday
        if (!in(date('N', strtotime($this->date)), '6,7')
            && !Indi::model('Holiday')->fetchRow('`title` = "' . $this->date . '"')
            && ($discount = Indi::blocks('work-day-discount'))
            && ((!$until = Indi::blocks('discount-until-time')) || $this->foreign('timeId')->title < $until)) {

            // Apply discount
            $this->price *= 1 - $discount/100;
        }

        // Call parent
        return parent::save();
    }

    /**
     * Set info about whether a manager created this entry, or some another kind of user
     */
    public function onBeforeInsert() {
        $m = Indi::admin()->alternate == 'manager';
        $this->requestByManagerId = $m ? Indi::admin()->id : 0;
        $this->requestBy = $m ? 'manager' : 'client';
        $this->requestDate = date('Y-m-d H:i:s');
    }

    /**
     * Prevent creation info from being modified for existing entries
     */
    public function onBeforeUpdate() {
        foreach (ar('requestBy,requestByManagerId,requestDate') as $prop) unset($this->_modified[$prop]);
    }

    /**
     * Confirm event
     */
    public function confirm($managerId, $prepay){

        // Shortcut for event's district
        $districtR = $this->foreign('districtId');

        // Assign confirmation-related props
        $this->assign(array(
            'managePrepay' => $prepay,
            'manageManagerId' => $managerId,
            'manageStatus' => '120#00ff00',
            'manageDate' => date('Y-m-d'),
            'clientAgreementNumber' => $districtR->code . str_pad($districtR->lastAgreement + 1, 4, '0', STR_PAD_LEFT)
        ));

        // Save assigned props
        parent::save();

        // Increment agreement counter
        $districtR->lastAgreement++;
        $districtR->save();
    }

    /**
     *
     */
    public function setSpaceSince() {
        $this->spaceSince = $this->date . ' ' . $this->foreign('timeId')->title . ':00';
    }

    /**
     *
     */
    public function setSpaceUntil() {
        $this->spaceUntil = date('Y-m-d H:i:s', strtotime($this->spaceSince) + $this->foreign('placeId')->duration * 60);
    }
}