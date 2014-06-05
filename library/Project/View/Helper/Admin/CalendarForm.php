<?php
class Indi_View_Helper_Admin_CalendarForm extends Indi_View_Helper_Abstract{
    public function calendarForm()
    {
        ob_start();
        $header = $this->view->formHeader();
        $header = str_replace("'#trail'", "'#trail-dev-null'", $header);
        echo $header;
        foreach ($this->view->trail->getItem()->fields as $field) {
            if(!$field->getForeignRowByForeignKey('elementId')->hidden) echo $this->view->formField($field);
        }
        ?></table>
        </form>
        <script><?=$this->view->trail->getItem()->section->javascriptForm?></script><?
        $parent = $this->view->trail->getItem(1);
        $actionA = $this->view->trail->getItem()->actions->toArray();
        $a = array();
        foreach ($actionA as $actionI) {
            if ($actionI['alias'] == 'save' && ($this->view->row->manageStatus != '000#980000' || !$_SESSION['admin']['alternate'])) {
                $a[] = "{
                    text: 'Сохранить',
                    handler: function(){
                         $('form[name=" . $this->view->entity->table . "]').submit()
                    },
                    iconCls: 'save',
                    id: 'button-save'
                }";
            }
            if ($actionI['alias'] == 'delete' && $this->view->row->id && ($this->view->row->manageStatus == '240#0000ff' || !$_SESSION['admin']['alternate'])) {
                $a[] = "{
                    text: 'Удалить',
                    handler: function(){
                        Ext.MessageBox.show({
                            title: 'Удаление',
                            msg: 'Вы уверены?',
                            buttons: Ext.MessageBox.YESNO,
                            icon: Ext.MessageBox.QUESTION,
                            fn: function(answer, arg2){
                                if (answer == 'yes') {
                                    var aix = top.window.eventStore.getById(" . $this->view->row->id . ").index + 1;
                                    $.get(PRE + '/" . $this->view->section->alias . "/delete/id/" . $this->view->row->id . "/ph/' + Indi.trail.item().section.primaryHash + '/aix/' + aix + '/', function(){
                                        top.window.form.close();
                                        top.window.eventStore.reload();
                                    });
                                }
                            }
                        });
                    },
                    iconCls: 'delete',
                    id: 'button-delete'
                }";
            }
            if ($actionI['alias'] == 'confirm' && $this->view->row->id && $this->view->row->manageStatus == '240#0000ff') {
                $managerRs = Misc::loadModel('Manager')->fetchAll();
                $options = array(); foreach($managerRs as $managerR) $options[] = array('id' => $managerR->id, 'title' => $managerR->title);
                $a[] = "{
                    text: 'Подтвердить',
                    handler: function(){
                        Ext.MessageBox.show({
                            title: 'Подтверждение заявки',
                            msg: '<span id=\"msgbox-prepay\"></span><span id=\"managerSelectBox\"></span><br/><br/><br/>',
                            buttons: Ext.MessageBox.OKCANCEL,
                            icon: Ext.MessageBox.INFO,
                            width: 300,
                            fn: function(answer, arg2){
                                if (answer == 'ok') {
                                    var aix = top.window.eventStore.getById(" . $this->view->row->id . ").index + 1;
                                    $.post(PRE + '/" . $this->view->section->alias . "/confirm/id/" . $this->view->row->id . "/ph/' + Indi.trail.item().section.primaryHash + '/aix/' + aix + '/',
                                        {managePrepay: Ext.getCmp('managePrepay').getValue(), manageManagerId: Ext.getCmp('manageManagerId').getValue()},
                                        function(response){
                                            Ext.MessageBox.show({
                                                title:'Подтверждение заявки',
                                                msg: 'Заявка подтверждена',
                                                buttons: Ext.MessageBox.OK,
                                                icon: Ext.MessageBox.INFO,
                                                fn: function(){
													$('form[name=" . $this->view->entity->table . "]').submit();
                                                    top.window.eventStore.reload();
                                                    top.window.Ext.getCmp('button-confirm').hide();
                                                    top.window.Ext.getCmp('button-delete').hide();
                                                    top.window.Ext.getCmp('button-agreement').show();
//													top.window.Indi.load(top.window.Indi.pre + '/' + Indi.trail.item().section.alias + '/agreement/id/' + Indi.trail.item().row.id + '/');
                                                }
                                            });
                                        }
                                    )
                                }
                            }
                        });
                        Ext.create('Ext.form.field.ComboBox',{
                          store: Ext.create('Ext.data.Store', {
                                fields: ['id', 'title'],
                                data : " . json_encode($options) . "
                          }),
                          displayField: 'title',
                          valueField: 'id',
                          value: '" . $_SESSION['admin']['id'] . "',
                          typeAhead: false,
                          fieldLabel: 'Кем принята',
                          labelWidth: 90,
                          width: 210,
                          style: 'font-size: 10px',
                          cls: 'subsection-select',
                          editable: false,
                          renderTo: 'managerSelectBox',
                          id: 'manageManagerId'
                        });
                        Ext.create('Ext.form.field.Number',{
                          fieldLabel: 'Предоплата',
                          labelWidth: 90,
                          width: 180,
                          minValue: 0,
                          height: 19,
                          value: 500,
                          id: 'managePrepay',
                          renderTo: 'msgbox-prepay',
                          margin: '0 0 5 0'
                        });
                    },
                    id: 'button-confirm'
                }";
            }
            if ($actionI['alias'] == 'confirm' && !$this->view->row->id) {
                $managerRs = Misc::loadModel('Manager')->fetchAll();
                $options = array(); foreach($managerRs as $managerR) $options[] = array('id' => $managerR->id, 'title' => $managerR->title);
                $a[] = "{
                    text: 'Подтвердить',
                    handler: function(){
                        Ext.MessageBox.show({
                            title: 'Подтверждение заявки',
                            msg: '<span id=\"msgbox-prepay\"></span><span id=\"managerSelectBox\"></span><br/><br/><br/>',
                            buttons: Ext.MessageBox.OKCANCEL,
                            icon: Ext.MessageBox.INFO,
                            width: 300,
                            fn: function(answer, arg2){
                                if (answer == 'ok') {
                                    var data = {};
                                    $('input, textarea').each(function(){data[$(this).attr('name')] = $(this).val();});
                                    $.post($('form[name=" . $this->view->entity->table . "]').attr('action') + '?confirm', data, function(response){
                                        var lastOptions = top.window.eventStore.lastOptions;
                                        var eventsStore = top.window.eventStore;
                                        Ext.apply(lastOptions, {
                                            callback: function(records, options) {
                                                var aix = eventsStore.getById(parseInt(response.id)).index + 1;
                                                $.post(PRE + '/" . $this->view->section->alias . "/confirm/id/'+response.id+'/ph/' + Indi.trail.item().section.primaryHash + '/aix/' + aix + '/',
                                                    {managePrepay: Ext.getCmp('managePrepay').getValue(), manageManagerId: Ext.getCmp('manageManagerId').getValue()},
                                                    function(){
                                                        Ext.MessageBox.show({
                                                            title:'Подтверждение заявки',
                                                            msg: 'Заявка подтверждена',
                                                            buttons: Ext.MessageBox.OK,
                                                            icon: Ext.MessageBox.INFO,
                                                            fn: function(){
                                                                top.window.form.close();
                                                                top.window.Indi.load(top.window.Indi.pre + '/' + 
                                                                    Indi.trail.item().section.alias + 
                                                                    '/agreement/id/' + response.id + 
                                                                    '/ph/' + Indi.trail.item().section.primaryHash + '/aix/' + aix + '/', true);
                                                            }
                                                        });
                                                    }
                                                )
                                            }
                                        });                                        
                                        top.window.eventStore.reload(lastOptions);
                                    }, 'json');
                                }
                            }
                        });
                        Ext.create('Ext.form.field.ComboBox',{
                          store: Ext.create('Ext.data.Store', {
                                fields: ['id', 'title'],
                                data : " . json_encode($options) . "
                          }),
                          displayField: 'title',
                          valueField: 'id',
                          value: '" . $_SESSION['admin']['id'] . "',
                          typeAhead: false,
                          fieldLabel: 'Кем принята',
                          labelWidth: 90,
                          width: 210,
                          style: 'font-size: 10px',
                          cls: 'subsection-select',
                          editable: false,
                          renderTo: 'managerSelectBox',
                          id: 'manageManagerId'
                        });
                        Ext.create('Ext.form.field.Number',{
                          fieldLabel: 'Предоплата',
                          labelWidth: 90,
                          width: 180,
                          minValue: 0,
                          height: 19,
                          value: 500,
                          id: 'managePrepay',
                          renderTo: 'msgbox-prepay',
                          margin: '0 0 5 0'
                        });
                    },
                    id: 'button-confirm'
                }";
            }
            if ($actionI['alias'] == 'agreement' && $this->view->row->id) {
                $a[] = "{
                    text: 'Договор',
                    handler: function(){
                        top.window.form.close();
                        var aix = top.window.eventStore.getById(" . $this->view->row->id . ").index + 1;
                        top.window.Indi.load(PRE + '/" . $this->view->section->alias . "/agreement/id/" . $this->view->row->id . "/ph/' + Indi.trail.item().section.primaryHash + '/aix/' + aix + '/', true);
                    },
                    id: 'button-agreement',
                    hidden: " . ($this->view->row->manageStatus == '240#0000ff' ? 'true' : 'false') . "
                }";
            }
            if ($actionI['alias'] == 'cancel' && $this->view->row->id) {
                $a[] = "{
                    text: 'Отменить',
                    handler: function(){
                        var aix = top.window.eventStore.getById(" . $this->view->row->id . ").index + 1;
						$.post(PRE + '/" . $this->view->section->alias . "/cancel/id/" . $this->view->row->id . "/ph/' + Indi.trail.item().section.primaryHash + '/aix/' + aix + '/',
							{},
							function(response){
								Ext.MessageBox.show({
									title:'Отмена заявки',
									msg: response == 'ok' ? 'Заявка отменена' : 'Отменять можно только подтвержденные заявки',
									buttons: Ext.MessageBox.OK,
									icon: response == 'ok' ? Ext.MessageBox.INFO : Ext.MessageBox.WARNING,
									fn: function(){
										top.window.form.close();
										top.window.eventStore.reload();
									}
								});
							}
						)
                    },
                    id: 'button-cancel',
                    hidden: " . ($this->view->row->manageStatus == '120#00ff00' ? 'false' : 'true') . "
                }";
            }
        }
        if (count($a)){?>
        <script>
        var toolbar = {
            xtype: 'toolbar',
            dock: 'top',
            id: 'topbar',
            items: [<?=implode(', ', $a)?>]
        }
        var topbar = top.window.form.getDockedComponent('topbar');
        if (topbar) top.window.form.removeDocked(topbar);
        top.window.form.addDocked(toolbar);
        </script>
        <?}?>
        <script>
        top.window.$('#calendar-form-panel').css('height', '100%');
        var height = top.window.Ext.getCmp('calendar-form-panel').getHeight();
        top.window.$('iframe[name="form-frame"]').css('height', height + 'px');
        </script>
        <? return ob_get_clean();
    }
}