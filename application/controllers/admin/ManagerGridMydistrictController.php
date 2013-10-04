<?php
class Admin_ManagerGridMydistrictController extends Project_Controller_Admin_EventsGrid{
    public function confirmAction(){
        if ($this->row->manageStatus == '120#00ff00') {
            $response = 'already';
        } else if ($this->post['managePrepay']){
            $this->row->managePrepay = $this->post['managePrepay'];
            $this->row->manageManagerId = $this->post['manageManagerId'] ? $this->post['manageManagerId'] : $_SESSION['admin']['id'];
            $this->row->manageStatus = '#00ff00';
            $this->row->manageDate = date('Y-m-d');
            $this->row->save();
            $this->row->setAgreementNumber();
            $response = 'Заявка отмечена как подтвержденная';
        } else {
            $managerRs = Misc::loadModel('Manager')->fetchAll('`districtId` = "' . $this->row->districtId . '"');
            $options = array(); foreach($managerRs as $managerR) $options[] = '<option value="' . $managerR->id . '"' . ($managerR->id == $_SESSION['admin']['id'] ? ' selected="selected"' : '') .'>' . $managerR->title . '</option>';
            $response = '<span id="msgbox-prepay"></span><select id="manageManagerId">' . implode('', $options) . '</select><br/><br/><br/>';
        }
        die($response);
    }

    public function agreementAction(){
        if ($this->params['check'] && $this->row->manageStatus != '120#00ff00') {
            die('not-confirmed');
        }
    }
    
    public function assign(){
        $data = $this->trail->items[1]->actions->toArray();
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['alias'] == 'delete') {
                $data[$i]['javascript'] = 'if(grid.store.getById(row.id).get("manageStatus").match(/Подтвержденная/)){
                    Ext.MessageBox.show({
                      title:"Удаление заявки невозможно",
                      msg: "Нельзя удалять подтвержденные заявки",
                      buttons: Ext.MessageBox.OK,
                      icon: Ext.MessageBox.WARNING
                    });                    
                } else {
                    ' . $data[$i]['javascript'] .'
                }';
            }
        }
        $this->trail->items[1]->actions->setData($data);
        parent::assign();
    }
}