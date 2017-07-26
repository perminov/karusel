<?php
class Project_Controller_Admin_Calendar extends Indi_Controller_Admin_Calendar {

    public function adjustEventForMonth($r) {
        $r->title = Indi::rexm('/] (.*)$/', $r->title, 1);
    }

    public function adjustGridData(&$data) {
        for ($i = 0; $i < count($data); $i++) {

            // Set color
            //$data[$i]['cid'] = $this->setColor($data[$i]);

            // Add exclaim, if need
            $title = $this->_exclaim($data[$i]);

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

    public function setColor1($item) {
        if (preg_match('/Подтвержденная/', $item['manageStatus'])) {
            return 2;
        } else if (preg_match('/Проведенная/', $item['manageStatus'])) {
            return 4;
        } else if (preg_match('/Отмененная/', $item['manageStatus'])) {
            return 5;
        } else if ($item['requestBy'] == 'Заказчиком') {
            return 3;
        } else {
            return 1;
        }
    }

    public function _exclaim($item) {
        return;
        // Получать данные о количествах подпрограмм и необходимых аниматоров нужно только один раз
        if (!$this->subprogramsCount) {

            // Получаем данные о том, сколько подпрограмм в каждой программе
            $programA = Indi::db()->query('SELECT `title`, `subprogramsCount` FROM `program`')->fetchAll();
            foreach($programA as $programI) $this->subprogramsCount[$programI['title']] = $programI['subprogramsCount'];

            // Получаем данные о том, сколько аниматоров в каждой подпрограмме
            $subprogramA = Indi::db()->query('SELECT `title`, `animatorsCount` FROM `subprogram`')->fetchAll();
            foreach($subprogramA as $subprogramI) $this->animatorsCount[$subprogramI['title']] = $subprogramI['animatorsCount'];
        }

        if ($item['cid'] == 2) {
            if (!$item['programId']) {
                $e = true;
            } else {
                if ($this->subprogramsCount[$item['programId']] > 0) {
                    if (!$item['subprogramId']) {
                        $e = true;
                    } else if ($this->animatorsCount[$item['subprogramId']] > count(explode(',', $item['animatorId']))) {
                        $e = true;
                    } else if (!$item['animatorId']) {
                        $e = true;
                    } else {
                        $e = false;
                    }
                } else {
                    if (!$item['animatorId']) {
                        $e = true;
                    } else {
                        $e = false;
                    }
                }
            }
        } else {
            $e = false;
        }

        // Append 'C' letter (this was requested by customer)
        if ($this->type == 'month' && ($item['details'] || $item['manageNotes']))
            $n =  '<span style="color:#cc00ff; font-weight: bold;">C</span>';

        // Return
        return $n . ($e ? '<span style="color:red; font-weight: bold;">!</span> ' : '');
    }
}