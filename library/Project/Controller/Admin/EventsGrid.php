<?php
class Project_Controller_Admin_EventsGrid extends Project_Controller_Admin{
    public function setGridTitlesByCustomLogic(&$data){
        $ids = array(); for ($i = 0; $i < count($data); $i++) $ids[] = $data[$i]['id'];
        $priceA = $this->db->query('SELECT `id`, `modifiedPrice` FROM `event` WHERE FIND_IN_SET(`id`, "' . implode(',', $ids) . '")')->fetchAll();
        $mPrice = array(); foreach ($priceA as $priceI) if ($priceI['modifiedPrice']) $mPrice[$priceI['id']] = $priceI['modifiedPrice'];
        
        for ($i = 0; $i < count($data); $i++) {
            preg_match('/([0-9]{4}\-[0-9]{2}\-[0-9]{2})/', $data[$i]['title'], $date);
            $data[$i]['title'] = str_replace($date[1], date('d.m.Y', strtotime($date[1])), $data[$i]['title']);
            $data[$i]['price'] = $mPrice[$data[$i]['id']] ? $mPrice[$data[$i]['id']] : $data[$i]['price'];
            if($data[$i]['manageDate'] == '0000-00-00') $data[$i]['manageDate'] = '';
            $data[$i]['requestDate'] = current(explode(' ', $data[$i]['requestDate']));
            $data[$i]['manageStatus'] = preg_replace('/Подтвержденная|Предварительная/', '', $data[$i]['manageStatus']);
            $data[$i]['manageStatus'] = preg_replace('/style=[\'"]/', '$0 margin-left: 10px; ', $data[$i]['manageStatus']);
        }

        parent::setGridTitlesByCustomLogic($data);
    }
}