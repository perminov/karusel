Ext.define('Indi.controller.places', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Title: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$MaxChildrenCount: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    minValue: 3
                })
            }
        }
    }
});