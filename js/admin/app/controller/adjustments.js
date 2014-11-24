Ext.define('Indi.controller.adjustments', {
    extend: 'Indi.Controller',
    actionsConfig: {
        index: {
            panel: {
                docked: {
                    default: {minHeight: 27},
                    items: [{alias: 'filter'}],
                    inner: {
                        filter: [{alias: 'keyword'}]
                    }
                }
            },
            rowset: {
                features: [{
                    ftype: 'grouping',
                    groupHeaderTpl: '{name}'
                }]
            },
            panelDocked$Filter$Keyword: function() {
                return this.panelDockedInner$Keyword();
            },
            rowsetInner$Excel: function() {
                return {disabled: true};
            },
            store: {
                groupField: 'datetime'
            },
            storeLoadCallbackDataRowAdjust: function(r) {
                r.set('datetime', r.get('datetime') + ' - ' + r.get('author'));
            },
            gridColumn$Author: function(column) {
                column = null;
            },
            gridColumn$Datetime: function(column) {
                column = null;
            },
            gridColumn$FieldId: function(column) {
                return Ext.merge(column, {
                    groupable: false,
                    sortable: false,
                    menuDisabled: true,
                    header: 'Что'
                });
            },
            gridColumn$Was: function(column) {
                return Ext.merge(column, {
                    groupable: false,
                    sortable: false,
                    menuDisabled: true,
                    renderer: function(value) {
                        return value;
                    }
                })
            },
            gridColumn$Now: function(column) {
                return Ext.merge(column, {
                    groupable: false,
                    sortable: false,
                    menuDisabled: true,
                    renderer: function(value) {
                        return value;
                    }
                })
            }
        }
    }
});