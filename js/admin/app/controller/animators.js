Ext.define('Indi.controller.animators', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Email: function(item) {
                return Ext.merge(item, {
                    fieldLabel: 'Логин'
                })
            }
        }
    }
});