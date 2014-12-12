Ext.define('Indi.controller.managers', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Email: function(item) {
                return Ext.merge(item, {
                    fieldLabel: 'Логин',
                })
            },
            formItem$Password: function(item) {
                return Ext.merge(item, {
                    minLength: 7
                })
            }
        }
    }
});