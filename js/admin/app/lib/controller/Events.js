Ext.define('Indi.lib.controller.Events', {
    extend: 'Indi.lib.controller.Controller',
    actionsSharedConfig: {
        _btn$Confirm: function(response, row, success) {
            var me = this;
            Ext.MessageBox.show({
                title: 'Подтверждение мероприятия',
                msg: 'Укажите менеджера, заключившего договор с клиентом и размер предоплаты',
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.INFO,
                form: {
                    defaults: {$ctx: me},
                    margin: '20 0 0 0',
                    items: [{
                        allowBlank: false,
                        xtype: 'combo.auto',
                        fieldLabel: 'Менеджер',
                        name: 'manageManagerId',
                        value: (me.ti().row && me.ti().row.manageManagerId) || ''
                    }, {
                        xtype: 'numberfield',
                        name: 'managePrepay',
                        allowBlank: false,
                        fieldLabel: 'Предоплата',
                        cls: 'i-field',
                        minValue: 0,
                        value: 500,
                        afterSubTpl: '<span class="i-field-number-after" style="margin-left: 58px;">руб</span>',
                        width: 160
                    }],
                    listeners: {
                        afterrender: function(form) {
                            form.up('messagebox').msgButtons['ok'].setDisabled(true);
                            this.items.each(function(item){
                                if (item.xtype == 'combo.auto') item.loadStore();
                            });
                        },
                        validitychange: function(form, valid) {
                            form.owner.up('messagebox').msgButtons['ok'].setDisabled(!valid);
                        },
                        destroy: function(form) {
                            if (Ext.Msg.msgButtons['ok']) Ext.Msg.msgButtons['ok'].setDisabled(false);
                        }
                    }
                },
                fn: function(answer) {
                    if (answer == 'ok') Ext.Ajax.request({
                        url: response.request.options.url,
                        method: 'POST',
                        params: this.down('form').getForm().getValues(),
                        success: success
                    });
                },
                scope: Ext.MessageBox
            });
        }
    },
    actionsConfig: {
        index: {
            gridColumn$FinalPrice: {summaryType: 'sum'},
            panelDockedInner$Actions$Confirm_InnerHandler: function(action,row, aix, btn) {
                var me = this;
                this.panelDockedInner$Actions_DefaultInnerHandlerLoad(action, row, aix, btn, {
                    success: function(response) {
                        me._btn$Confirm(response, row, function(response) {
                            var json = Ext.JSON.decode(response.responseText, true);
                            if (me.affectRecord) me.affectRecord(row, json);
                        })
                    }
                });
            }
        },
        form: {
            panelDockedInner$Actions$Confirm: {
                handler: function(btn) {
                    var me = btn.ctx();
                    me.goto(me.other('confirm'), false, {success: function(response){
                        me._btn$Confirm(response, me.ti().row, function() {
                            Ext.Msg.on('hide', function(){
                                Ext.getCmp(this.panelDockedInnerBid() + 'reload').press();
                            }, me, {single: true, delay: 500});
                        });
                    }})
                }
            },
            panelDockedInner$Actions$Cancel: {
                handler: function(btn) {
                    var me = btn.ctx();
                    me.goto(me.other('cancel'), false, {success: function(){
                        Ext.Msg.on('hide', function(){
                            Ext.getCmp(this.panelDockedInnerBid() + 'reload').press();
                        }, me, {single: true, delay: 500});
                    }});
                }
            }
        },
        print: {
            panelDockedInner$Actions$Confirm: {
                handler: function(btn) {
                    var me = btn.ctx();
                    me.goto(me.other('confirm'), false, {success: function(response){
                        me._btn$Confirm(response, me.ti().row, function() {
                            Ext.Msg.on('hide', function(){
                                Ext.getCmp(this.panelDockedInnerBid() + 'reload').press();
                            }, me, {single: true, delay: 500});
                        });
                    }})
                }
            },
            panelDockedInner$Actions$Cancel: {
                handler: function(btn) {
                    var me = btn.ctx();
                    me.goto(me.other('cancel'), false, {success: function(){
                        Ext.Msg.on('hide', function(){
                            Ext.getCmp(this.panelDockedInnerBid() + 'reload').press();
                        }, me, {single: true, delay: 500});
                    }});
                }
            }
        }
    }
});