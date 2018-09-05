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

    /**
     * Confirm event
     */
    public function confirmAction() {

        // If current event's status is not 'preview' - flush error message
        if ($this->row->manageStatus != 'preview') jflush(false, I_EVENT_ERR_CANT_CONFIRM);

        // Else if $_POST data is given
        else if (array_key_exists('manageManagerId', $data = Indi::post())) {

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
                'msg' => sprintf(I_EVENT_STATUS_CHANGED_TO, $this->row->foreign('manageStatus')->title),
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
        if (!in($this->row->manageStatus, 'confirmed,preview')) jflush(false, I_EVENT_ERR_CANT_CANCEL);

        // Change `manageStatus` to  'cancelled'
        $this->row->manageStatus = 'cancelled';
        $this->row->save();

        // Flush success with affected data
        jflush(array(
            'success' => true,
            'msg' => sprintf(I_EVENT_STATUS_CHANGED_TO, $this->row->foreign('manageStatus')->title),
            'affected' => $this->affected()
        ));
    }

    /**
     * Delete event
     */
    public function deleteAction() {

        // Check that current `manageStatus` is appropriate for event to be deleted
        if (in($this->row->manageStatus, 'confirmed,archive')) jflush(false, I_EVENT_ERR_CANT_DELETE);

        // Call parent
        $this->callParent();
    }
}