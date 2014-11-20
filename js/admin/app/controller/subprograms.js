Ext.define('Indi.controller.subprograms', {
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