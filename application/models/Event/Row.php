<?php
class Event_Row extends Indi_Db_Table_Row {

    public function schedule($data = array()) {

        // Check given data against validation rules
        $this->mcheck(array(
            'placeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => true
            ),
            array_key_exists('since', $data) ? 'since,until' : 'date' => array(
                'req' => true,
                'rex' => 'date'
            ),
            'duration' => array(
                'rex' => 'int11'
            )
        ), $data);

        // Create schedule
        $schedule = array_key_exists('since', $data)
            ? Indi::schedule($this->since, $this->until)
            : Indi::schedule('month', $this->date);

        // Get daily working hours
        $daily = $this->daily();

        // Set daily working hours and load existing events
        $schedule->load('event', array(
            '`placeId` = "' . $this->placeId . '"',
            '`id` != "' . $this->id . '"',
            '`manageStatus` != "cancelled"'
        ))->daily($daily['since'], $daily['until']);

        // Return $schedule
        return $schedule;
    }

    public function busyDates($data = array()) {

        // Get schedule
        $schedule = $this->schedule($data);

        // Get desired space frame
        $frame = $this->duration . 'm';

        // Get busy dates
        $busyA = $schedule->busyDates($frame);

        // If $data arg was given - just return busy dates
        if (func_num_args()) return $busyA;

        // Else if current `date` is a totally busy date - set mismatch message
        else if (in($this->date, $busyA)) $this->mflush('date', 'Эта дата полностью занята');
    }

    public function busyTimes($data = array()) {

        // Get schedule
        $schedule = $this->schedule($data);

        // Get desired space frame
        $frame = $this->duration . 'm';

        // Get busy hours
        $busyA = $schedule->busyHours($frame, $this->date, '30m');

        // Get list of invalid values of timeId` prop
        $busyTimeIdA = Indi::model('Time')->fetchAll('FIND_IN_SET(`title`, "' . im($busyA) . '")')->column('id');

        // If $data arg was given - just return busy time ids
        if (func_num_args()) return $busyTimeIdA;

        // Else if current `timeId` is a totally busy time - set mismatch message
        if (in($this->timeId, $busyTimeIdA)) $this->mflush('timeId', 'Это время недоступно');
    }

    public function busyAnimators($data = array()) {

        // Check $data arg
        $this->mcheck(array(
            'placeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Place'
            ),
            'date' => array(
                'req' => true,
                'rex' => 'date'
            ),
            'timeId' => array(
                'req' => true,
                'rex' => 'int11',
                'key' => 'Time'
            ),
            'duration' => array(
                'rex' => 'int11'
            )
        ), $data);

        // General WHERE clause
        $where = array(
            '`manageStatus` != "cancelled"',
            '`placeId` != "' . $this->placeId . '"',
            '`id` != "' . $this->id . '"',
        );

        // Get daily hours
        $daily = $this->daily();

        // If $data arg is given
        if (func_num_args()) {

            // Append additional clause
            $where[] = '`date` = "' . $this->date . '" AND `animatorId` != ""';

            // Get animators, involved in events at date, specified by $data['date']
            $animatorIdA = array_unique(ar($this->model()->fetchAll($where)->column('animatorId', true, true)));

            // Remove additional clause
            array_pop($where);

        // Else use current value of `animatorId` prop
        } else $animatorIdA = $this->zero('animatorId') ? array() : ar($this->animatorId);

        // If `animatorId` prop not yet set
        // Foreach animator, involved in events at date, specified by $data['date']
        $busyA = array(); foreach ($animatorIdA as $animatorId) {

            // Append animator-related clause
            $where[] = 'FIND_IN_SET("' . $animatorId . '", `animatorId`)';

            // Create schedule, set daily active hours and load animator's events
            $schedule = Indi::schedule('week', $this->date)
                ->load('event', $where)
                ->daily($daily['since'], $daily['until']);

            // Remove animator-related clause
            array_pop($where);

            // If animator is busy - push it's id to $busyAnimatorA array
            if ($schedule->busy(
                $this->date . ' ' . $this->foreign('timeId')->title . ':00',
                $this->duration * 60, true, true
            )) $busyA[] = $animatorId;
        }

        // If $data arg was given - just return busy time ids
        if (func_num_args()) return $busyA;

        // If one or more of selected animators are busy - flush error message
        else if (count($busyA)) $this->mflush('animatorId', 'Уже занятые аниматоры: '
            . $this->foreign('animatorId')->select($busyA)->column('title', ', '));
    }

    public function validate() {

        // Check that event's date is not busy
        $this->busyDates();

        // Check that event's time is not busy
        $this->busyTimes();

        // If `programId` is non-zero - check that event's animators are not busy
        if (!$this->zero('animatorId')) $this->busyAnimators();

        // Call parent
        return $this->callParent();
    }

    /**
     * @return int|mixed
     */
    public function onBeforeSave() {

        // Set `title`
        $this->title = sprintf('[%s, %s] %s: %s', $this->date, $this->foreign('timeId')->title,
            $this->foreign('districtId')->code, $this->foreign('placeId')->title);

        // Set child age
        if ($this->isModified('birthChildBirthDate')) $this->birthChildAge
            = $this->zero('birthChildBirthDate') ? 0 : date('Y') - $this->date('birthChildBirthDate', 'Y');

        // Set price
        $this->price();
    }

    public function price($data = array()) {

        // Check
        $this->mcheck(array(
            'programId' => array('rex' => 'int11', 'key' => true),
            'subprogramId' => array('rex' => 'int11', 'key' => true)
        ), $data);

        // Animators qty
        $aQty = $this->foreign('subprogramId')->animatorsCount ?: 1;

        // Set `districtId`
        if (!$this->districtId) $this->districtId = $this->foreign('placeId')->districtId;

        // Get initial price
        $this->price = $this->foreign('districtId')->{'price' . $aQty};

        // If event date is not a saturday/sunday and is not an other holiday
        if (!in(date('N', strtotime($this->date)), '6,7')
            && !Indi::model('Holiday')->fetchRow('`title` = "' . $this->date . '"')
            && ($discount = Indi::blocks('work-day-discount'))
            && ((!$until = Indi::blocks('discount-until-time')) || $this->foreign('timeId')->title < $until)) {

            // Apply discount
            $this->price *= 1 - $discount/100;
        }

        // Set `finalPrice`
        $this->finalPrice = $this->modifiedPrice ?: $this->price;

        // Return price
        return $this->price;
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
            'manageStatus' => 'confirmed',
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
     * Ignore `mobile` and `workphone` while fixing types of data, got from PDO
     *
     * @param array $data
     * @return array
     */
    public function fixTypes(array $data) {

        // Foreach prop check
        foreach ($data as $k => $v) if (!in($k, 'clientPhone')){

            // If prop's value is a string, containing integer value - force value type to be integer, not string
            if (preg_match(Indi::rex('int11'), $v)) $data[$k] = (int) $v;

            // Else if prop's value is a string, containing decimal value - force value type to be float, not string
            else if (preg_match(Indi::rex('decimal112'), $v)) $data[$k] = (float) $v;

            // Else if prop's value is a string, containing relative src - prepend STD
            else if ($m = Indi::rexm('~\burl\((/[^/]+)~', $v)) $data[$k] = preg_replace('~\burl\((/[^/]+)~', 'url(' . STD . '$1', $v);
        }

        // Return
        return $data;
    }
}