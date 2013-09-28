<?php
class Project_Controller_Admin_Calendar extends Indi_Controller_Admin{
    /**
     * We set ORDER as 'id', as we do not need any other order type
     * @return string
     */
    public function getOrderForJsonRowset(){
        return 'timeId';
    }

    /**
     * Here we add a WHERE clause parts to retrieve rows related to a given period of time
     * @param $condition
     * @return array|string
     */
    public function modifyRowsetCondition($condition) {
        $condition = $condition ? explode(' AND ', $condition) : array();
        $start = explode('-', $this->get['start']);
        $end = explode('-', $this->get['end']);
        $condition[] = '`calendarStart` >= "' . $start[2] . '-' . $start[0] . '-' . $start[1]. ' 00:00:00"';
        $condition[] = '`calendarEnd` <= "' . $end[2] . '-' . $end[0] . '-' . $end[1]. ' 23:59:59"';
        $condition = implode(' AND ', $condition);
        return $condition;
    }

    /**
     * Prevent redirection after form save
     */
    public function saveAction(){
        parent::saveAction(false);
    }

    public function setGridTitlesByCustomLogic(&$data) {
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]['start'] = $data[$i]['calendarStart'];
            $data[$i]['end'] = $data[$i]['calendarEnd'];
            $data[$i]['cid'] = $this->setColor($data[$i]);
            $data[$i]['title'] = $data[$i]['placeId'];
        }
    }

    public function setColor($item) {
        if (preg_match('/Подтвержденная/', $item['manageStatus'])) {
            return 2;
        } else if ($item['requestBy'] == 'Заказчиком') {
            return 3;
        } else {
            return 1;
        }
    }
}