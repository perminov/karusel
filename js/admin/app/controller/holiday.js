Ext.define('Indi.controller.holiday', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Title: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    minValue: new Date()
                })
            }
        }
    }
});