Ext.define('Indi.controller.programs', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Title: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            }
        }
    }
});