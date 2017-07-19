Ext.define('Indi.controller.list', {
    extend: 'Indi.lib.controller.Events',
    actionsConfig: {
        index: {
            panelDockedInner$Actions$Agreement: {
                iconCls: '!i-btn-icon-print'
            }
        },
        form: {
            panel: {
                docked: {
                    inner: {
                        master: [
                            {alias: 'ID'},
                            {alias: 'reload'}, '-',
                            {alias: 'save'}, {alias: 'autosave'}, '-',
                            {alias: 'reset'}, '-',
                            {alias: 'prev'}, {alias: 'sibling'}, {alias: 'next'}, '-',
                            {alias: 'agreement'}, '-',
                            {alias: 'create'}, '-',
                            {alias: 'nested'}, '->',
                            {alias: 'offset'}, {alias: 'found'}
                        ]
                    }
                }
            },
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
                                Ext.Ajax.request({
                                    url: Indi.std + '/auxiliary/disabledAnimators/',
                                    params: {
                                        timeId: d.timeId,
                                        date: c.sbl('date').getSubmitValue(),
                                        placeId: d.placeId,
                                        animatorsNeededCount: !c.sbl('subprogramId').hasZeroValue()
                                            ? c.sbl('subprogramId').prop('animatorsCount')
                                            : 1
                                    },
                                    success: function(response) {
                                        var json = Ext.JSON.decode(response.responseText, true);
                                        if (Ext.isObject(json)) {
                                            c.setDisabledOptions(json.disabled);
                                            c.maxSelected = !c.sbl('subprogramId').hasZeroValue()
                                                ? c.sbl('subprogramId').prop('animatorsCount')
                                                : 1;
                                            c.sbl('price').val(json.price);
                                        }
                                    }
                                });
                                Indi.load('/' + me.ti().section.alias + '/form' + (me.ti().row.id ? '/id/' + me.ti().row.id : '') + '/consider/animatorId/', {
                                    params: {
                                        timeId: d.timeId,
                                        date: c.sbl('date').getSubmitValue(),
                                        placeId: d.placeId,
                                        animatorsNeededCount: !c.sbl('subprogramId').hasZeroValue()
                                            ? c.sbl('subprogramId').prop('animatorsCount')
                                            : 1
                                    },
                                    success: function(response) {
                                        var json = response.responseText.json();
                                    }
                                });
                            } else c.disable();
                        }
                    }
                });
            },
            panelDockedInner$Agreement: function() {

                // Here we check if 'save' action is in the list of allowed actions
                var me = this;

                // 'Save' item config
                return {
                    id: me.panelDockedInnerBid() + 'agreement',
                    xtype: 'button',
                    tooltip: 'Договор',
                    iconCls: 'i-btn-icon-print',
                    handler: function() {
                        me.goto(me.other('agreement'));
                    }
                }
            }
        },
        agreement: {
            formItemXSpan: function() {
                return Ext.merge(this.callParent(), {
                    value: 'Договор на проведение мероприятия'
                });
            },
            formItem$Editor: function() {
                var me = this; return {
                    editorCfg: {
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