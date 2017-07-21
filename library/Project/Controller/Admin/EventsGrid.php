<?php
class Project_Controller_Admin_EventsGrid extends Project_Controller_Admin_Events {

    /**
     *
     */
    public function adjustGridDataRowset() {

        // Fetch foreign data for `subprogramId` key
        $this->rowset->foreign('subprogramId');
    }

    /**
     * @param array $data
     */
    public function adjustGridData(&$data) {

        // Foreach $data
        for ($i = 0; $i < count($data); $i++) {

            // Change format of date, mentioned within `title` from 'Y-m-d' to 'd.m.Y'
            if (array_key_exists('title', $data[$i])) $data[$i]['title']
                = preg_replace('/([0-9]{4})\-([0-9]{2})\-([0-9]{2})/', '$3.$2.$1', $data[$i]['title']);

            // Use subprogram title instead of program title, if subprogram is specified
            if (array_key_exists('programId', $data[$i])) $data[$i]['programId']
                = $this->rowset->at($i)->foreign('subprogramId')->title ?: $data[$i]['programId'];
        }

        // Call parent
        parent::adjustGridData($data);
    }
}