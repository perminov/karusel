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

            formItem$PlaceId: function(item) {
                var me = this;
                return Ext.merge(item, {
                    listeners: {
                        afterrender: function(c) {
                        },
                        change: function() {
                            console.log('change');
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