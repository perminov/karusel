<?php
class Project_Controller_Admin_Calendar extends Project_Controller_Admin{
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
            $data[$i]['title'] = $this->setExclaim($data[$i]) . $data[$i]['placeId'];
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

    public function setExclaim($item) {
        // Получать данные о количествах подпрограмм и необходимых аниматоров нужно только один раз
        if (!$this->subprogramsCount) {

            // Получаем данные о том, сколько подпрограмм в каждой программе
            $programA = $this->db->query('SELECT `title`, `subprogramsCount` FROM `program`')->fetchAll();
            foreach($programA as $programI) $this->subprogramsCount[$programI['title']] = $programI['subprogramsCount'];

            // Получаем данные о том, сколько аниматоров в каждой подпрограмме
            $subprogramA = $this->db->query('SELECT `title`, `animatorsCount` FROM `subprogram`')->fetchAll();
            foreach($subprogramA as $subprogramI) $this->animatorsCount[$subprogramI['title']] = $subprogramI['animatorsCount'];
        }

        if ($item['cid'] == 2) {
            if (!$item['programId']) {
                $e = true;
            } else {
                if ($this->subprogramsCount[$item['programId']] > 0) {
                    if (!$item['subprogramId']) {
                        $e = true;
                    } else if ($this->animatorsCount[$item['subprogramId']] > count(explode(',', $item['animatorIds']))) {
                        $e = true;
                    } else if (!$item['animatorIds']) {
                        $e = true;
                    } else {
                        $e = false;
                    }
                } else {
                    if (!$item['animatorIds']) {
                        $e = true;
                    } else {
                        $e = false;
                    }
                }
            }
        } else {
            $e = false;
        }

        return $e ? '<span style="color:red; font-weight: bold;">!</span> ' : '';
    }
}