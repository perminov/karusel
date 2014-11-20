Ext.define('Indi.controller.animators', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Title: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$Email: function(item) {
                return Ext.merge(item, {
                    fieldLabel: 'Логин',
                    allowBlank: false
                })
            },
            formItem$Password: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            }
        }
    }
});