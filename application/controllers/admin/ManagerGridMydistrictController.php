<?php
class Admin_ManagerGridMydistrictController extends Project_Controller_Admin_EventsGrid{
    public function confirmAction(){
        if ($this->row->manageStatus == '120#00ff00') {
            $response = 'already';
        } else if (Indi::post('managePrepay')){
            $this->row->managePrepay = Indi::post('managePrepay');
            $this->row->manageManagerId = Indi::post('manageManagerId') ? Indi::post('manageManagerId') : $_SESSION['admin']['id'];
            $this->row->manageStatus = '#00ff00';
            $this->row->manageDate = date('Y-m-d');
            $this->row->save();
            $this->row->setAgreementNumber();
            $response = 'Заявка отмечена как подтвержденная';
        } else {
            $managerRs = Indi::model('Manager')->fetchAll();
            $options = array(); foreach($managerRs as $managerR) $options[] = '<option value="' . $managerR->id . '"' . ($managerR->id == $_SESSION['admin']['id'] ? ' selected="selected"' : '') .'>' . $managerR->title . '</option>';
            $response = '<span id="msgbox-prepay"></span><select id="manageManagerId">' . implode('', $options) . '</select><br/><br/><br/>';
        }
        die($response);
    }

    public function postDispatch($return = false) {
        foreach (Indi::trail()->actions as $actionR) {
            if ($actionR->alias == 'delete') {
                $actionR->javascript = 'if(indi.action.index.store.getById(row.id).get("manageStatus").match(/#00ff00/)){
                    Ext.MessageBox.show({
                      title:"Удаление заявки невозможно",
                      msg: "Нельзя удалять подтвержденные заявки",
                      buttons: Ext.MessageBox.OK,
                      icon: Ext.MessageBox.WARNING
                    });
                } else {
                    ' . $actionR->javascript . '
                }';
            }
        }
        return parent::postDispatch($return);
    }
}