Ext.define('Indi.controller.districts', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Email: function(item) {
                return Ext.merge(item, {
                    vtype: 'email'
                })
            }
        }
    }
});