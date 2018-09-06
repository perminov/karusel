<?php
class Event_Row extends Indi_Db_Table_Row {

    /**
     * @return array|mixed
     */
    public function validate() {

        // Skip custom validation if needed
        if ($this->noValidate) return $this->callParent();

        // Check placeId is not zero
        $this->mcheck(array('placeId' => array('req' => true)));

        // Check space-fields
        foreach ($this->disabled(false) as $prop => $disabledA)
            foreach ($disabledA as $disabledI)
                if (in($disabledI, $this->$prop))
                    $this->_mismatch[$prop] .= $disabledI;

        // If any mismatches detected - call parent
        if ($this->_mismatch) return $this->callParent();

        // Call parent
        return $this->callParent();
    }

    /**
     * Setup fields, that are linked to space owners
     *
     * @return array
     */
    protected function _spaceOwners() {

        // Return
        return array(
            'placeId' => array(
                'rex' => 'int11'
            ),
            'animatorId' => array(
                'rex' => 'int11list',
                'pre' => function($r){
                    $r->spaceFrame = _2sec('1h');
                }
            )
        );
    }

    /**
     * @param array $data
     * @return array
     */
    public function disabled($data = array()) {

        // Setup $strict flag indicating whether or not 'req'-rule
        // should be added for each space-coord field's validation rules array.
        $strict = !(is_array($data) || $data instanceof ArrayObject); if ($strict) $data = array();

        // Get space-coord fields and their validation rules
        $spaceCoords = $this->_spaceCoords($strict);

        // Setup validation rules for $data['since'] and $data['until']
        $schedBounds = $strict ? array() : array('since,until' => array('rex' => 'date'));

        // Get space-owners fields
        $spaceOwners = $this->_spaceOwners();

        // Get rules for satellite-fields, e.g. fields that space-owner fields rely on
        $ownerRelyOn = array();
        foreach (array_keys($spaceOwners) as $owner) if ($sFieldR = $this->field($owner)->satellite())
            if ($sra = $sFieldR->storeRelationAbility) $ownerRelyOn[$sFieldR->alias]
                = array('rex' => $sra == 'many' ? 'int11list' : 'int11');

        // Validate all involved fields
        $this->mcheck($spaceCoords + $schedBounds + $spaceOwners + $ownerRelyOn, $data);

        // Create schedule
        $schedule = !$strict && array_key_exists('since', $this->_temporary)
            ? Indi::schedule($this->since, strtotime($this->until . ' +1 day'))
            : Indi::schedule('month', $this->fieldIsZero('date') ? null : $this->date);

        // Preload existing events, but do not fill schedule with them
        $schedule->preload($this->_table, array(
            '`id` != "' . $this->id . '"',
            '`manageStatus` != "cancelled"'
        ));

        // Expand schedule's right bound
        $schedule->frame($frame = $this->_spaceFrame());

        // Collect distinct values for each prop
        $schedule->distinct(array_keys($spaceOwners));

        // Get daily working hours
        $daily = $this->daily(); $disabled = array('date' => array(), 'timeId' => array());

        // Get time in 'H:i' format
        $time = $this->foreign('timeId')->title;

        // Setup 'early' and 'late' spaces and backup
        $schedule->daily($daily['since'], $daily['until'])->backup();

        // Foreach prop, representing event-participant
        foreach ($spaceOwners as $prop => $ruleA) {

            // Declare $prop-key within $disabled array, and initially set it to be an empty array
            $disabled[$prop] = $busy['date'] = $busy['time'] = $psblA = array();

            // Get distinct values of current $prop from schedule's rowset,
            // as they are values that do have a probability to be disabled
            // So, for each $prop's distinct value
            foreach ($schedule->distinct($prop) as $id => $idxA) {

                // Reset $both array
                $both = array();

                // Refill schedule
                $schedule->refill($idxA, null, null, $ruleA['pre']);

                // Collect info about disabled values per each busy date
                // So for each busy date we will have the exact reasons of why it is busy
                // Also, fulfil $both array with partially busy dates
                foreach ($dates = $schedule->busyDates($frame, $both) as $date)
                    $busy['date'][$date][] = $id;

                // Get given date's busy hours for current prop's value
                if ($both) foreach ($both as $date)
                    foreach ($schedule->busyHours($date, '30m', true) as $Hi)
                        $busy['time'][$date][$Hi][] = $id;
            }

            // If we have values, fully busy at at least one day
            if ($busy['date']) {

                // Get array of possible values
                $psblA = $this->getComboData($prop)->column('id');

                // For each date, that busy for some values,
                foreach ($busy['date'] as $date => $busyA) {

                    // Reset $d flag to `false`
                    $d = false;

                    // If there are no possible values remaining after
                    // deduction of busy values - set $d flag to `true`
                    if (!array_diff($psblA, $busyA)) $d = true;

                    // Else if current value of $prop is given, but it's
                    // in the list of busy values - also set $d flag to `true`
                    else if (preg_match('~,(' . im($busyA, '|') . '),~', ',' . $this->$prop . ',')) $d = true;

                    // If $d flag is `true` - append disabled date
                    if ($d) $disabled['date'][$date] = true;

                    // If iterated date is same as current date - append disabled value for $prop prop
                    if ($date == $this->date) $disabled[$prop] = $busyA;
                }
            }

            // If we have values, fully busy at at least one day
            if ($busy['time']) {

                // Get array of possible values, keeping in mind
                // that some values might have already been excluded by date
                if (!$psblA) $psblA = $this->getComboData($prop)->column('id');

                // For each date, that busy for some values,
                foreach ($busy['time'] as $date => $HiA) {

                    // If there are non-empty array of disabled values for entry's time
                    if ($busyA = $HiA[$time]) {

                        // Reset $d flag to `false`
                        $d = false;

                        // If there are no possible values remaining after
                        // deduction of busy values - set $d flag to `true`
                        if (!array_diff($psblA, array_merge($busy['date'][$date] ?: [], $busyA))) $d = true;

                        // Else if current value of $prop is given, but it's
                        // in the list of busy values - also set $d flag to `true`
                        else if (preg_match('~,(' . im($busyA, '|') . '),~', ',' . $this->$prop . ',')) $d = true;

                        // If $d flag is `true` - append disabled date
                        if ($d) $disabled['date'][$date] = true;

                        // If iterated date is same as current date - append disabled value for $prop prop
                        if ($date == $this->date) $disabled[$prop] = array_merge($disabled[$prop] ?: [], $busyA);
                    }

                    // If iterated date is same as current date - append disabled value for `timeId` prop
                    if ($date == $this->date) foreach ($HiA as $Hi => $busyA) {

                        // Reset $d flag to `false`
                        $d = false;

                        // If there are no possible values remaining after
                        // deduction of busy values - set $d flag to `true`
                        if (!array_diff($psblA, array_merge($busy['date'][$date] ?: [], $busyA))) $d = true;

                        // Else if current value of $prop is given, but it's
                        // in the list of busy values - also set $d flag to `true`
                        else if (preg_match('~,(' . im($busyA, '|') . '),~', ',' . $this->$prop . ',')) $d = true;

                        // If $d flag is `true` - append disabled `timeId`
                        if ($d && $timeId = timeId($Hi)) $disabled['timeId'][$timeId] = true;
                    }
                }
            }
        }

        // Append disabled timeIds, for time that is before opening and after closing
        if ($daily['since'] || $daily['until']) foreach (timeId() as $Hi => $timeId) {
            $His = $Hi . ':00';
            if ($daily['since'] && $His <  $daily['since']) $disabled['timeId'][$timeId] = true;
            if ($daily['until'] && $His >= $daily['until']) $disabled['timeId'][$timeId] = true;
        }

        // Use keys as values for date and timeId
        foreach ($disabled as $prop => $data) {
            if ($prop == 'date') $disabled[$prop] = array_keys($data);
            if ($prop == 'timeId') $disabled[$prop] = array_keys($data);
        }

        // Return info about disabled values
        return $disabled;
    }

    /**
     * @return int|mixed
     */
    public function onBeforeSave() {

        // Set `title`
        $this->setTitle();

        // Set client agreement number
        $this->setClientAgreementNumber();

        // Set child age
        if ($this->isModified('birthChildBirthDate')) $this->birthChildAge
            = $this->zero('birthChildBirthDate') ? 0 : date('Y') - $this->date('birthChildBirthDate', 'Y');

        // Check is there a problem with this event
        $this->problem();

        // Set price
        $this->price();
    }

    /**
     * Setting title is wrapped into a separate method because `title` field depend on localized fields
     */
    public function setTitle() {

        // Set `title`
        $this->title = sprintf('[%s, %s] %s: %s', $this->date, $this->foreign('timeId')->title,
            $this->foreign('districtId')->code, $this->foreign('placeId')->title);
    }

    /**
     * Calc price, depending on animators count, event's weekday & start time
     *
     * @param array $data
     * @return float|string
     */
    public function price($data = array()) {

        // Check
        $this->mcheck(array(
            'districtId,placeId,programId,subprogramId,timeId' => array('rex' => 'int11', 'key' => true),
            'date' => array('rex' => 'date')
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
            $this->price = (int) $this->price;
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
            'clientAgmtIdx' => str_pad($districtR->lastAgreement + 1, 4, '0', STR_PAD_LEFT),
        ));

        // Set client agreement number
        $this->setClientAgreementNumber();

        // Save assigned props
        parent::save();

        // Increment agreement counter
        $districtR->lastAgreement++;
        $districtR->save();
    }

    /**
     *
     */
    public function setClientAgreementNumber() {
        $this->clientAgreementNumber = $this->clientAgmtIdx ? $this->foreign('districtId')->code . $this->clientAgmtIdx : '';
    }

    /**
     * Set `problem` flag to in case if this is a confirmed event, but:
     * 1. No program chosen
     * 2. No animators chosen
     * 3. No subprogram chosen (in case if program has subprograms)
     * 4. Qty of chosen animators is not equal to qty required by chosen program/subprogram
     */
    public function problem() {

        // Set no problem, by default
        $p = false;

        // If event is confirmed
        if ($this->manageStatus == 'confirmed') {

            // If no program yet chosen - set $e to `true`, else
            if (!$this->programId) $p = true; else {

                // If no animators yet assigned
                if (!$this->animatorId) $p = true;

                // If event's program has subprograms
                else if ($this->foreign('programId')->subprogramsCount > 0) {

                    // If no subprogram yet specified - set exclaim
                    if (!$this->subprogramId) $p = true;

                    // Else
                    else if ($this->foreign('subprogramId')->animatorsCount
                        > count(ar($this->animatorId))) $p = true;
                }
            }
        }

        // Set `problem` flag
        $this->problem = $p ? 'y' : 'n';
    }

    /**
     * Ignore `mobile` and `workphone` while fixing types of data, got from PDO
     *
     * @param array $data
     * @return array
     */
    public function fixTypes(array $data) {

        // Foreach prop check
        foreach ($data as $k => $v) if (!in($k, 'clientPhone,clientPhone2')){

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