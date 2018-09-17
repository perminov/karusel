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
        foreach ($this->spaceDisabledValues(false) as $prop => $disabledA)
            foreach ($disabledA as $disabledI)
                if (in($disabledI, $this->$prop))
                    $this->_mismatch[$prop] .= $disabledI;

        // If any mismatches detected - call parent
        if ($this->_mismatch) return $this->callParent();

        // Call parent
        return $this->callParent();
    }

    /**
     * WHERE clause to be used for event-entries preloading
     *
     * @return array
     */
    public function spacePreloadWHERE() {
        return array('`manageStatus` != "cancelled"');
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