Ext.define('Indi.controller.list', {
    extend: 'Indi.Controller',
    actionsConfig: {
        index: {
            rowset: {
                firstColumnWidthFraction: 0.23
            },
            panelDockedInner$Actions$Agreement: function(item) {
                return Ext.merge(item, {
                    iconCls: 'i-btn-icon-print',
                    tooltip: item.text,
                    text: ''
                });
            },
            panelDocked$Filter$PlaceId: function(filter) {
                filter.store.js = '';
                return filter;
            },
            panelDocked$Filter$AnimatorIds: function(filter) {
                filter.store.js = '';
                return filter;
            }
        },
        form: {
            panel: {
                docked: {
                    inner: {
                        master: [
                            {alias: 'back'}, '-',
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
            formItemDefault: function(item) {
                var me = this;
                return Ext.merge(me.callParent(arguments), {
                    allowBlank: true
                });
            },
            formItem$DistrictId: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    listeners: {
                        change: function(c) {
                            if (parseInt(c.val())) {
                                /*if ($('#maxChildrenCount').length) {
                                 Ext.getCmp('ext-childrenCount-slider').setMaxValue(parseInt($(this).attr('maxChildrenCount')));
                                 if (parseInt($('#childrenCount').val()) > parseInt($(this).attr('maxChildrenCount'))) {
                                 $('#childrenCount').val($(this).attr('maxChildrenCount'));
                                 }
                                 $('#maxChildrenCount').text($(this).attr('maxChildrenCount'));
                                 }*/
                            }
                        }
                    }
                });
            },
            formItem$PlaceId: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    listeners: {
                        change: function(c) {
                            if (parseInt(c.val())) {
                                /*if ($('#maxChildrenCount').length) {
                                    Ext.getCmp('ext-childrenCount-slider').setMaxValue(parseInt($(this).attr('maxChildrenCount')));
                                    if (parseInt($('#childrenCount').val()) > parseInt($(this).attr('maxChildrenCount'))) {
                                        $('#childrenCount').val($(this).attr('maxChildrenCount'));
                                    }
                                    $('#maxChildrenCount').text($(this).attr('maxChildrenCount'));
                                }*/
                            }
                        }
                    }
                });
            },
            formItem$Date: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    considerOn: [{
                        name: 'placeId',
                        required: true
                    }],
                    listeners: {
                        enablebysatellite: function(c, data) {
                            Ext.Ajax.request({
                                url: Indi.std + '/auxiliary/disabledDates/',
                                params: data,
                                success: function(response) {
                                    var json = Ext.JSON.decode(response.responseText, true);
                                    if (Ext.isArray(json)) c.setDisabledDates(json.length ? json : ["2000-01-01"]);
                                }
                            });
                        }
                    }
                });
            },
            formItem$TimeId: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    considerOn: [{
                        name: 'date',
                        required: true
                    }, {
                        name: 'placeId',
                        required: true
                    }],
                    listeners: {
                        enablebysatellite: function(c, data) {
                            Ext.Ajax.request({
                                url: Indi.std + '/auxiliary/disabledTimes/',
                                params: data,
                                success: function(response) {
                                    var json = Ext.JSON.decode(response.responseText, true);
                                    if (Ext.isArray(json)) c.setDisabledOptions(json);
                                }
                            });
                        }
                    }
                });
            },
            formItem$ProgramId: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    considerOn: [{
                        name: 'timeId',
                        required: true
                    }],
                    listeners: {
                        change: function(c) {
                            c.sbl('price').val(0);
                            if (c.hasZeroValue()) {
                                //c.sbl('subprogramId').hide();
                                top.window.$('.feature-item-5').css('height', '669px');
                            } else if (!c.sbl('subprogramId').disabled) {
                                c.sbl('subprogramId').show();
                                top.window.$('.feature-item-5').css('height', '714px');
                                c.sbl('animatorIds').disable().clearValue();
                            } else {
                                //c.sbl('subprogramId').hide();
                                top.window.$('.feature-item-5').css('height', '669px');
                            }
                        }
                    }
                });
            },
            formItem$AnimatorIds: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    considerOn: [
                        {name: 'timeId', required: true}, {name: 'programId', required: true},
                        {name: 'date', required: true}, {name: 'placeId', required: true}, {name: 'subprogramId'}
                    ],
                    listeners: {
                        enablebysatellite: function(c, data) {
                            if (c.sbl('subprogramId').disabled || !c.sbl('subprogramId').hasZeroValue()) {
                                Ext.Ajax.request({
                                    url: Indi.std + '/auxiliary/disabledAnimators/',
                                    params: {
                                        timeId: c.sbl('timeId').val(),
                                        date: c.sbl('date').getSubmitValue(),
                                        placeId: c.sbl('placeId').val(),
                                        animatorsNeededCount: !c.sbl('subprogramId').hasZeroValue() ? 1 : 1
                                    },
                                    success: function(response) {
                                        var json = Ext.JSON.decode(response.responseText, true);
                                        if (Ext.isObject(json)) {
                                            c.setDisabledOptions(json.disabled);
                                            c.sbl('price').val(json.price);
                                        }
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
            formItem$Print: function() {
                var me = this; return Ext.merge(me.callParent(), {
                    editorCfg: {
                        style: 'body {background: url('+ Indi.std + '/i/admin/bg-dogovor.jpg) no-repeat 50% 95%;} ' +
                            '* {font-family:Verdana, Arial, Helvetica, sans-serif; font-size:10.4px; line-height: 12px !important;}',
                    },
                    listeners: {
                        boxready: function() {
                            Ext.util.Cookies.set('last-row-id', me.ti().row.id, Ext.Date.add(new Date(), Ext.Date.MONTH, 1), Indi.pre + '/');
                        }
                    }
                });
            }
        },
        form1: {

 /* --animatorIds--
  if($(this).parents('table').find('span.checkbox.checked').length < parseInt($('#programId').attr('animatorsCount'))) {
    $(this).parents('table').find('span.checkbox').parents('tr').not('.disabled').show();
} else {
    $(this).parents('table').find('span.checkbox').not('.checked').parent().parent().hide();
}  */

/* --placeId--           if ($(this).val() != "0") {
    $('#date').removeAttr('disabled');
    $('#date').parents('.calendar-div').removeClass('disabled');
    $('#date').val('').change();

    $('#timeId-keyword').val('');
    $('#timeId').val(0).change();

    Indi.combo.form.toggle('timeId', true);
    $.post(Indi.std+'/auxiliary/disabledDates/',{placeId: $('#placeId').val()}, function(disabledDates){
        if(disabledDates.length) {
            Ext.getCmp('dateCalendar').setDisabledDates(disabledDates);
        } else {
            Ext.getCmp('dateCalendar').setDisabledDates(["2000-01-01"]);
        }
    }, 'json');
    if ($('#maxChildrenCount').length) {
        Ext.getCmp('ext-childrenCount-slider').setMaxValue(parseInt($(this).attr('maxChildrenCount')));
        if (parseInt($('#childrenCount').val()) > parseInt($(this).attr('maxChildrenCount'))) {
            $('#childrenCount').val($(this).attr('maxChildrenCount'));
        }
        $('#maxChildrenCount').text($(this).attr('maxChildrenCount'));
    }
} else {
    $('#date').attr('disabled','disabled');
    $('#date').parents('.calendar-div').addClass('disabled');
    $('#date').val('').change();

    $('#timeId-keyword').val('');
    $('#timeId').val(0).change();
    Indi.combo.form.toggle('timeId',true);

    $('#programId').removeAttr('animatorsCount');
}*/
        }
    }
});