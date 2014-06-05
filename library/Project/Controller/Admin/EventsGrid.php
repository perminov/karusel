<?php
class Project_Controller_Admin_EventsGrid extends Project_Controller_Admin{
    public function preDispatch() {
        if ($this->params['action'] == 'index' && $this->params['json']) {
//          $this->db->query('UPDATE `event` SET `manageStatus` = "060#ffff00" WHERE `manageStatus` = "120#00ff00" AND `date` < CURDATE();');
            $this->db->query('UPDATE `event` SET `manageStatus` = "000#980000" WHERE `manageStatus` = "120#00ff00" AND `date` < CURDATE();');
        }
        parent::preDispatch();
    }
    public function setGridTitlesByCustomLogic(&$data){
        $ids = array(); for ($i = 0; $i < count($data); $i++) $ids[] = $data[$i]['id'];
        $infoA = $this->db->query('SELECT `id`, `modifiedPrice`, `subprogramId` FROM `event` WHERE FIND_IN_SET(`id`, "' . implode(',', $ids) . '")')->fetchAll();
        $mPrice = $subprogramIdA = array(); 
        foreach ($infoA as $infoI) {
            if ($infoI['modifiedPrice']) $mPrice[$infoI['id']] = $infoI['modifiedPrice'];
            if ($infoI['subprogramId']) $subprogramIdA[$infoI['id']] = $infoI['subprogramId'];
        }
        $subprogramA = Misc::loadModel('Subprogram')->fetchAll('FIND_IN_SET(`id`, "' . implode(',', array_values($subprogramIdA)) . '")')->toArray();
        $subprogramTitleA = array();
        foreach ($subprogramA as $subprogramI) {
            if ($found = array_search($subprogramI['id'], $subprogramIdA)) {
                $subprogramTitleA[$found] = $subprogramI['title'];
            }
        }
        
        for ($i = 0; $i < count($data); $i++) {
            preg_match('/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/', $data[$i]['title'], $date);
            $data[$i]['title'] = str_replace($date[1], date('d.m.Y', strtotime($date[1])), $data[$i]['title']);
            $data[$i]['price'] = $mPrice[$data[$i]['id']] ? $mPrice[$data[$i]['id']] : $data[$i]['price'];
            $data[$i]['programId'] = $subprogramTitleA[$data[$i]['id']] ? $subprogramTitleA[$data[$i]['id']] : $data[$i]['programId'];
            if($data[$i]['manageDate'] == '0000-00-00') $data[$i]['manageDate'] = '';
            $data[$i]['manageStatus'] = preg_replace('/Подтвержденная|Предварительная|Проведенная|Отмененная/', '', $data[$i]['manageStatus']);
            $data[$i]['manageStatus'] = preg_replace('/style=[\'"]/', '$0 margin-left: 10px; ', $data[$i]['manageStatus']);
        }

        parent::setGridTitlesByCustomLogic($data);
    }
	public function cancelAction(){
		if ($this->row->manageStatus != '120#00ff00') {
			$response = 'forbidden';
		} else {
			$this->row->manageStatus = '#ff9900';
			$this->row->save();
			$response = 'ok';
		}
		die($response);
	}
}