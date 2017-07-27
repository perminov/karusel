<?php
class Project_Controller_Admin_Events extends Project_Controller_Admin {

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