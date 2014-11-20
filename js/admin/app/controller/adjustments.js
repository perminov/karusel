Ext.define('Indi.controller.adjustments', {
    extend: 'Indi.Controller',
    actionsConfig: {
        index: {
            panel: {
                /**
                 * Docked items special config
                 */
                docked: {
                    default: {minHeight: 27},
                    items: [{alias: 'filter'}],
                    inner: {
                        filter: [{alias: 'keyword'}]
                    }
                }
            },
            panelDocked$Filter$Keyword: function() {
                return this.panelDockedInner$Keyword();
            },
            rowset: {
                docked: {
                    items: [{alias: 'paging'}],
                    inner: {
                        paging: []
                    }
                },
                features: [{
                    ftype: 'grouping',
                    groupHeaderTpl: '{name}'
                }]
            },
            store: {
                groupField: 'grouperField'
            },
            storeLoadCallbackDataRowAdjust: function(r) {
                r.set('grouperField', r.get('datetime') + ' - ' + r.get('author'));
            },
            initComponent: function() {
                var me = this; me.ti().gridFields.unshift({
                    alias: "grouperField", alternative: "", columnTypeId: "1", defaultValue: "", dependency: "u",
                    elementId: "1", entityId: "309", filter: "", id: "grouperField", javascript: "", move: "2181",
                    relation: "0", satellite: "0", satellitealias: "", storeRelationAbility: "none", title: ""
                });
                me.callParent();
            },
            gridColumn$FieldId: function(column) {
                return Ext.merge(column, {
                    header: 'Что'
                });
            },
            gridColumn$GrouperField: function(column) {
                column = null;
            },
            gridColumn$Author: function(column) {
                column = null;
            },
            gridColumn$Datetime: function(column) {
                column = null;
            },
            gridColumn$Was: function(column) {
                return Ext.merge(column, {
                    renderer: function(value) {
                        return value;
                    }
                })
            },
            gridColumn$Now: function(column) {
                return Ext.merge(column, {
                    renderer: function(value) {
                        return value;
                    }
                })
            }
        }
    }
});