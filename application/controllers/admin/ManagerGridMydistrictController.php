<?php
class Admin_ManagerGridMydistrictController extends Project_Controller_Admin_EventsGrid{

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