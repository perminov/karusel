Ext.define('Indi.lib.controller.Events', {
    extend: 'Indi.lib.controller.Controller',
    actionsSharedConfig: {
        _btn$Confirm: function(response, row, success) {
            var me = this, uid = Indi.user.uid.split('-');
            Ext.MessageBox.show({
                title: Indi.lang.I_EVENT_CONFIRM_TITLE,
                msg: Indi.lang.I_EVENT_CONFIRM_MSG,
                buttons: Ext.MessageBox.OK,
                icon: Ext.MessageBox.INFO,
                form: {
                    defaults: {$ctx: me},
                    margin: '20 0 0 0',
                    items: [{
                        allowBlank: false,
                        xtype: 'combo.auto',
                        fieldLabel: Indi.lang.I_EVENT_CONFIRM_MANAGER,
                        name: 'manageManagerId',
                        value: uid[0] == '15' ? uid[1] : '',
                        minWidth: 250
                    }, {
                        xtype: 'numberfield',
                        name: 'managePrepay',
                        allowBlank: false,
                        fieldLabel: Indi.lang.I_EVENT_CONFIRM_PREPAYMENT,
                        cls: 'i-field',
                        minValue: 0,
                        value: 500,
                        afterSubTpl: '<span class="i-field-number-after" style="margin-left: 58px;">' + Indi.lang.I_CY + '</span>',
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
    actionsSharedConfig$Row: {
        panelDockedInner$Actions$Confirm: {
            handler: function(btn) {
                var me = btn.ctx();
                me.goto(me.other('confirm'), false, {success: function(response){
                    me._btn$Confirm(response, me.ti().row, function(response) {
                        me.affectRecord(response);
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
                me.goto(me.other('cancel'), false, {success: function(response){
                    me.affectRecord(response);
                    Ext.Msg.on('hide', function(){
                        Ext.getCmp(this.panelDockedInnerBid() + 'reload').press();
                    }, me, {single: true, delay: 500});
                }});
            }
        },
        disabledOptions: function(c, bounds) {
            var me = this, data = {}, v;
            ['placeId','date','timeId','animatorId','duration'].forEach(function(prop){
                data[prop] = c.sbl(prop).getSubmitValue();
                if (prop == 'date' && !data[prop].length) data[prop] = '0000-00-00';
            });
            if (bounds) Ext.Object.merge(data, bounds);
            Indi.load('/' + me.ti().section.alias + '/form' + (me.ti().row.id ? '/id/' + me.ti().row.id : '') + '/consider/duration/', {
                params: data,
                success: function(response) {
                    var disabled = response.responseText.json().disabled, arg, format;
                    for (var i in disabled) {
                        if (i != 'date') c.sbl(i).setDisabledOptions(disabled[i]); else {
                            format = c.sbl('date').format, arg = [];
                            if (disabled[i].length) disabled[i].forEach(function(d, i){
                                arg[i] = Ext.Date.format(new Date(d), format);
                            }); else arg.push('0001-01-01');
                            c.sbl(i).setDisabledDates(arg);
                        }
                    }
                }
            });
        }
    },
    actionsConfig: {
        index: {
            gridColumn$FinalPrice: {summaryType: 'sum'},
            gridColumn$ManageStatus: {allowCycle: false},
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
            formItem$PlaceId: {
                listeners: {
                    change: function(){
                        this.ctx().disabledOptions(this);
                    }
                }
            },
            formItem$Date: {
                listeners: {
                    blur: function() {
                        this.ctx().disabledOptions(this);
                    },
                    boundchange: function(c, bounds) {
                        this.ctx().disabledOptions(this, {
                            since: Ext.Date.format(bounds[0], 'Y-m-d'),
                            until: Ext.Date.format(bounds[1], 'Y-m-d')
                        });
                    },
                    change: function(){
                        this.ctx().disabledOptions(this);
                    }
                }
            },
            formItem$Duration: function(item){
                var me = this;
                return Ext.merge(item, {
                    listeners: {
                        enablebysatellite: function(c, data) {
                            me.disabledOptions(c);
                        },
                        change: function(){
                            this.ctx().disabledOptions(this);
                        }
                    }
                });
            },
            formItem$TimeId: {
                listeners: {
                    change: function(){
                        this.ctx().disabledOptions(this);
                    }
                }
            },
            formItem$ProgramId: {
                listeners: {
                    change: function(c) {
                        c.sbl('price').val(0);
                        if (c.hasZeroValue()) {
                            c.sbl('subprogramId').hide();
                        } else if (c.prop('subprogramsCount')) {
                            c.sbl('subprogramId').show();
                            c.sbl('animatorId').disable().clearValue();
                        } else {
                            c.sbl('subprogramId').hide();
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
                    listeners: {
                        change: function(){
                            this.ctx().disabledOptions(this);
                        },
                        enablebysatellite1: function(c, d) {
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
            }
        }
    }
});