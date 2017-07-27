<?php
class Project_Controller_Admin_Calendar extends Indi_Controller_Admin_Calendar {

    public function adjustEventForMonth($r) {
        $r->title = Indi::rexm('/] (.*)$/', $r->title, 1);
    }

    /*
     *
     */
    public function adjustGridData(&$data) {

        // Foreach data item
        for ($i = 0; $i < count($data); $i++) {

            // Add exclaim, if need
            $title = $this->_exclaim($data[$i], $i);

            // If 'Place' filter is used, or calendar type is 'day'
            if (preg_match('/placeId/', Indi::get('search')) || $this->type == 'day') {

                // Get client first name
                $client = current(array_slice(explode(' ', $data[$i]['clientTitle']), 1, 1));

                // Set initial title
                $title .= $client . ' ' . $data[$i]['clientPhone'] . ' ';
                $title .= '<span style="word-break: normal;">' . $data[$i]['childrenCount'] . '/' . ($data[$i]['childrenAge'] ?: '?') . '</span>; ';

                // Append manager and agreement number
                if ($manager = array_shift(explode(' ', $data[$i]['manageManagerId'])))
                    $title .= sprintf('<span style="word-break: normal;">%s</span>' . ' - %s; ',
                        $data[$i]['clientAgreementNumber'], $manager);

                // Append animator/program
                $title .= $this->_animprog($data[$i]);

                // Append `details` and `manageNotes`
                $title .= $data[$i]['details'];
                if ($data[$i]['manageNotes'])
                    $title .= sprintf('<span style="color: #8000A3;">%s</span> ', $data[$i]['manageNotes']);

            // Else
            } else {

                // Append district code
                if (!Indi::admin()->alternate) $title .= Indi::rexm('/([А-Я]{2}: )/u', $data[$i]['title'], 1);

                // Append place
                $title .= $data[$i]['placeId'];

                // Append animator/program info
                if (Indi::admin()->alternate == 'manager') $title .= $this->_animprog($data[$i]);
            }

            // Assign built title
            $data[$i]['title'] = $title;
        }
    }

    /**
     * @param $event
     * @return string
     */
    public function _animprog($event) {

        // Subprogram/program
        $prog = ($event['subprogramId'] ?: $event['programId']) . ' ';

        // If animators were assigned for this event
        if ($event['animatorId']) {

            // Append subprogram/program
            $title = $prog;

            // Append animators surnames
            $animSnameA = array();
            foreach (explode(', ', $event['animatorId']) as $anim)
                $animSnameA[] = array_shift(explode(' ', $anim));

            // Return
            return $title . '[' . implode(', ', $animSnameA) . '] ';

        // Else return subprogram/program highlighted with red color
        } else return sprintf('<span style="color: #cc0000;">%s</span> ', $prog);
    }

    /**
     * Set exclaim
     *
     * @param $item
     * @param $i
     * @return string
     */
    public function _exclaim($item, $i) {

        // Set no exclaim, by default
        $e = false;

        // If event is confirmed
        if ($item['$keys']['manageStatus'] == 'confirmed') {

            // If no program yet chosen - set $e to `true`, else
            if (!$item['programId']) $e = true; else {

                // If no animators yet assigned
                if (!$item['animatorId']) $e = true;

                // If event's program has subprograms
                else if ($this->rowset->at($i)->foreign('programId')->subprogramsCount > 0) {

                    // If no subprogram yet specified - set exclaim
                    if (!$item['subprogramId']) $e = true;

                    // Else
                    else if ($this->rowset->at($i)->foreign('subprogramId')->animatorsCount
                        > count(ar($item['animatorId']))) $e = true;
                }
            }
        }

        // Append 'C' letter (this was requested by customer)
        if ($this->type == 'month' && ($item['details'] || $item['manageNotes']))
            $n =  '<span style="color:#cc00ff; font-weight: bold;">C</span>';

        // Return
        return $n . ($e ? '<span style="color:red; font-weight: bold;">!</span> ' : '');
    }

    /**
     * @var bool
     */
    protected $_isRowsetSeparate = true;

    /**
     *
     */
    public function preDispatch() {

        // Set `manageStatus` as 'done' for yesterday and older events with status 'confirmed'
        if (Indi::uri()->action == 'index' && Indi::uri()->json) Indi::db()->query('
            UPDATE `event`
            SET `manageStatus` = "archive"
            WHERE 1
              AND `manageStatus` = "confirmed"
              AND `date` < CURDATE()
        ');

        // Call parent
        parent::preDispatch();
    }

    public function formActionIDate($data) {

        // Flush disabled dates
        jflush(true, array('disabledDates' => $this->row->busyDates($data)));
    }

    public function formActionITimeId($data) {

        // Flush busy time ids
        jflush(true, array('disabledTimeIds' => $this->row->busyTimes($data)));
    }

    public function formActionIAnimatorId($data) {

        // Flush busy time ids
        jflush(true, array('disabled' => $this->row->busyAnimators($data), 'price' => $this->row->price($data)));
    }

    /**
     * Confirm event
     */
    public function confirmAction() {

        // If current event's status is not 'preview' - flush error message
        if ($this->row->manageStatus != 'preview') jflush(false, 'Подтверждать можно только предварительные заявки');

        // Else if $_POST data is given
        else if (count($data = Indi::post())) {

            // Check data
            jcheck(array(
                'manageManagerId' => array(
                    'req' => true,
                    'rex' => 'int11',
                    'key' => 'Manager'
                ),
                'managePrepay' => array(
                    'rex' => 'int11'
                )
            ), $data);

            // Confirm event
            $this->row->confirm($data['manageManagerId'], $data['managePrepay']);

            // Assign row's grid data into 'affected' key within $response and flush success
            jflush(array(
                'success' => true,
                'msg' => sprintf('Статус мероприятия изменен на "%s"', $this->row->foreign('manageStatus')->title),
                'affected' => $this->affected()
            ));
        }

        // Flush success
        jflush(true);
    }

    /**
     * Cancel event
     */
    public function cancelAction() {

        // Check that current `manageStatus` is appropriate for event to be cancelled
        if (!in($this->row->manageStatus, 'confirmed,preview'))
            jflush(false, 'Отменять можно только мероприятия со статусом "Подтвержденное" или "Предварительное"');

        // Change `manageStatus` to  'cancelled'
        $this->row->manageStatus = 'cancelled';
        $this->row->save();

        // Flush success with affected data
        jflush(array(
            'success' => true,
            'msg' => sprintf('Статус мероприятия изменен на "%s"', $this->row->foreign('manageStatus')->title),
            'affected' => $this->affected()
        ));
    }

    /**
     * Delete event
     */
    public function deleteAction() {

        // Check that current `manageStatus` is appropriate for event to be deleted
        if (in($this->row->manageStatus, 'confirmed'))
            jflush(false, 'Нельзя удалять мероприятия, имеющие статус "Подтвержденное"');

        // Call parent
        $this->callParent();
    }
}