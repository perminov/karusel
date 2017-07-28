Ext.define('Indi.lib.controller.Events', {
    extend: 'Indi.lib.controller.Controller',
    actionsSharedConfig: {
        _btn$Confirm: function(response, row, success) {
            var me = this, uid = Indi.user.uid.split('-');
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
                        value: uid[0] == '15' ? uid[1] : ''
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
                            if (uid[0] != '15') form.up('messagebox').msgButtons['ok'].setDisabled(true);
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
            formItem$Date: function(item) {
                var me = this;
                return Ext.merge(item, {
                    considerOn: [{
                        name: 'placeId',
                        required: true
                    }],
                    listeners: {
                        boundchange: function(c) {
                            c.fireEvent('enablebysatellite', c, c.considerOnData());
                        },
                        enablebysatellite: function(c, d) {
                            var bounds = c.getPicker().getBounds();
                            Indi.load('/' + me.ti().section.alias + '/form' + (me.ti().row.id ? '/id/' + me.ti().row.id : '') + '/consider/date/', {
                                params: Ext.merge(d, {
                                    since: Ext.Date.format(bounds[0], 'Y-m-d'),
                                    until: Ext.Date.format(bounds[1], 'Y-m-d')
                                }),
                                success: function(response) {
                                    var dd = response.responseText.json().disabledDates;
                                    if (!dd.length) dd.push('0001-01-01');
                                    dd.forEach(function(d, i){
                                        dd[i] = Ext.Date.format(new Date(d), c.format);
                                    });
                                    c.setDisabledDates(dd);
                                }
                            });
                        }
                    }
                });
            },
            formItem$TimeId: function(item) {
                var me = this;
                return Ext.merge(item, {
                    considerOn: [{
                        name: 'date',
                        required: true
                    }, {
                        name: 'placeId',
                        required: true
                    }],
                    listeners: {
                        enablebysatellite: function(c, data) {
                            Indi.load('/' + me.ti().section.alias + '/form' + (me.ti().row.id ? '/id/' + me.ti().row.id : '') + '/consider/timeId/', {
                                params: data,
                                success: function(response) {
                                    var json = response.responseText.json();
                                    if (Ext.isArray(json.disabledTimeIds))
                                        c.setDisabledOptions(json.disabledTimeIds);
                                }
                            });
                        }
                    }
                });
            },
            formItem$ProgramId: {
                considerOn: [{
                    name: 'timeId',
                    required: true
                }],
                listeners: {
                    change: function(c) {
                        c.sbl('price').val(0);
                        if (c.hasZeroValue()) {
                            c.sbl('subprogramId').hide();
                            //top.window.$('.feature-item-5').css('height', '669px');
                        } else if (c.prop('subprogramsCount')) {
                            c.sbl('subprogramId').show();
                            //top.window.$('.feature-item-5').css('height', '714px');
                            c.sbl('animatorId').disable().clearValue();
                        } else {
                            c.sbl('subprogramId').hide();
                            //top.window.$('.feature-item-5').css('height', '669px');
                        }
                    }
                }
            },
            formItem$ChildrenCount: {
                considerOn: [{name: 'placeId'}],
                listeners: {
                    enablebysatellite: function(c, data) {
                        if (c.sbl('placeId').prop('maxChildrenCount'))
                            c.maxValue = c.sbl('placeId').prop('maxChildrenCount');
                    }
                }
            },
            formItem$AnimatorId: function(item) {
                var me = this;
                return Ext.merge(item, {
                    considerOn: [{
                        name: 'timeId',
                        required: true
                    }, {
                        name: 'programId',
                        required: true
                    }, {
                        name: 'date',
                        required: true
                    }, {
                        name: 'placeId',
                        required: true
                    }, {
                        name: 'subprogramId'
                    }],
                    listeners: {
                        enablebysatellite: function(c, d) {
                            if (!c.sbl('programId').prop('subprogramsCount') || !c.sbl('subprogramId').hasZeroValue()) {
                                Indi.load('/' + me.ti().section.alias + '/form' + (me.ti().row.id ? '/id/' + me.ti().row.id : '') + '/consider/animatorId/', {
                                    params: d,
                                    success: function(response) {
                                        var json = response.responseText.json();
                                        if (Ext.isObject(json)) {
                                            c.setDisabledOptions(json.disabled);
                                            c.maxSelected = !c.sbl('subprogramId').hasZeroValue()
                                                ? c.sbl('subprogramId').prop('animatorsCount')
                                                : 1;
                                            if (c.sbl('price')) c.sbl('price').val(json.price);
                                        }
                                    }
                                });
                            } else c.disable();
                        }
                    }
                });
            },
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
            formItemXSpan: function() {
                return Ext.merge(this.callParent(), {
                    value: 'Договор на проведение мероприятия'
                });
            },
            formItem$Editor: function() {
                var me = this; return {
                    editorCfg: {
                        readOnly: true,
                        style: 'body {background: url('+ Indi.std + '/i/admin/bg-dogovor.jpg) no-repeat 50% 95%;} ' +
                            '* {font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10.4px; line-height: 12px !important;}'
                    },
                    listeners: {
                        boxready: function() {
                            Ext.util.Cookies.set('last-row-id', me.ti().row.id, Ext.Date.add(new Date(), Ext.Date.MONTH, 1), Indi.pre + '/');
                        }
                    }
                };
            },
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