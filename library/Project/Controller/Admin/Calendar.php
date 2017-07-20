<?php
class Project_Controller_Admin_Calendar extends Project_Controller_Admin_Events{
    /**
     * We set ORDER as 'id', as we do not need any other order type
     * @return string
     */
    public function finalORDER(){
        return 'timeId';
    }

    /**
     * Here we add a WHERE clause parts to retrieve rows related to a given period of time
     * @param array $where
     * @return array|string
     */
    public function adjustPrimaryWHERE($where) {
        if (!Indi::get('start')) {
            $startDate = date('Y-m-01');
            $startTime = strtotime($startDate);
            $dayOfWeek = date('N', $startTime);
            Indi::get()->start = date('m-d-Y', $startTime - 60 * 60 * 24 * ($dayOfWeek - 1));
        }
        if (!Indi::get('end')) {
            $endDate = date('Y-m-' . date('t'));
            $endTime = strtotime($endDate);
            $dayOfWeek = date('N', $endTime);
            Indi::get()->end = date('m-d-Y', $endTime + 60 * 60 * 24 * (7 - $dayOfWeek /* " + 7" - убрать после окончания августа*/));
            //Indi::get()->end = date('m-d-Y', $endTime + 60 * 60 * 24 * (7 - $dayOfWeek + 7));
        }
        $start = explode('-', Indi::get('start'));
        $end = explode('-', Indi::get('end'));
        $where[] = '`calendarStart` >= "' . $start[2] . '-' . $start[0] . '-' . $start[1]. ' 00:00:00"';
        $where[] = '`calendarEnd` <= "' . $end[2] . '-' . $end[0] . '-' . $end[1]. ' 23:59:59"';
		//i($this->get, 'a');
        return $where;
    }

    /**
     * Prevent redirection after form save
     */
    public function saveAction(){
        parent::saveAction(false);
        if (array_key_exists('confirm', Indi::get())) {
            die(json_encode(array('id' => $this->row->id)));
        }
    }

    public function adjustGridData(&$data) {
        if (preg_match('/placeId/', Indi::get('search')) || Indi::get('start') == Indi::get('end')) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['start'] = $data[$i]['calendarStart'];
                $data[$i]['end'] = $data[$i]['calendarEnd'];
                $data[$i]['cid'] = $this->setColor($data[$i]);
                list($last, $client) = explode(' ', $data[$i]['clientTitle']);
                $title = $this->setExclaim($data[$i]) . $client . ' ' . $data[$i]['clientPhone'] . ' ';
                list($manager) = explode(' ', $data[$i]['manageManagerId']);
                $title .= '<span style="word-break: normal;">' . $data[$i]['childrenCount'] . '/' . $data[$i]['childrenAge'] . '</span>; ';
                if ($manager) {
                    $title .= '<span style="word-break: normal;">' . $data[$i]['clientAgreementNumber'] .'</span>' . ' - ' . $manager . '; ';
                }
                if ($data[$i]['animatorId']) {
                    $title .= ($data[$i]['subprogramId'] ? $data[$i]['subprogramId'] : $data[$i]['programId']) . ' ';
                    $animators = explode(', ', $data[$i]['animatorId']);
                    $lastA = array();
                    foreach ($animators as $animator) {
                        list($lastI) = explode(' ', $animator);
                        $lastA[] = $lastI;
                    }
                    $title .= '[' . implode(', ', $lastA) . '] ';
                } else {
                    $title .= '<span style="color: #cc0000;">';
                    $title .= ($data[$i]['subprogramId'] ? $data[$i]['subprogramId'] : $data[$i]['programId']) . ' ';
                    $title .= '</span> ';
                }
                $title .= $data[$i]['details'];
                
                if ($data[$i]['manageNotes']) {
                $title .= '<span style="color: #8000A3;">';
                $title .= $data[$i]['manageNotes'];
                $title .= '</span> ';
                }
                
                $data[$i]['title'] = $title;
            }
		} else if ($_SESSION['admin']['profileId'] == 15) {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['start'] = $data[$i]['calendarStart'];
                $data[$i]['end'] = $data[$i]['calendarEnd'];
                $data[$i]['cid'] = $this->setColor($data[$i]);
                $title = $this->setExclaim($data[$i]) . $data[$i]['placeId'] . ' ';
                if ($data[$i]['animatorId']) {
                    $title .= ($data[$i]['subprogramId'] ? $data[$i]['subprogramId'] : $data[$i]['programId']) . ' ';
                    $animators = explode(', ', $data[$i]['animatorId']);
                    $lastA = array();
                    foreach ($animators as $animator) {
                        list($lastI) = explode(' ', $animator);
                        $lastA[] = $lastI;
                    }
                    $title .= '[' . implode(', ', $lastA) . '] ';
                } else {
                    $title .= '<span style="color: #cc0000;">';
                    $title .= ($data[$i]['subprogramId'] ? $data[$i]['subprogramId'] : $data[$i]['programId']) . ' ';
                    $title .= '</span> ';
                }
				$data[$i]['title'] = $title;
            }
		} else {
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]['start'] = $data[$i]['calendarStart'];
                $data[$i]['end'] = $data[$i]['calendarEnd'];
                $data[$i]['cid'] = $this->setColor($data[$i]);
                $data[$i]['title'] = $this->setExclaim($data[$i]) . $data[$i]['placeId'];
            }
        }
    }

    public function setColor($item) {
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

    public function setExclaim($item) {
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

        if (strtotime(Indi::get('end')) > strtotime(Indi::get('start')) + 60 * 60 * 24 * 28)
            $n = $item['details'] || $item['manageNotes'] ? '<span style="color:#cc00ff; font-weight: bold;">C</span> ' : '';
            
        return $n . ($e ? '<span style="color:red; font-weight: bold;">!</span> ' : '');
    }
}