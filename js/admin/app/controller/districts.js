Ext.define('Indi.controller.districts', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Title: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$Code: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$Price1: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$Price2: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$Address: function(item) {
                return Ext.merge(item, {
                    allowBlank: false
                })
            },
            formItem$Email: function(item) {
                return Ext.merge(item, {
                    allowBlank: false,
                    vtype: 'email'
                })
            }
        }
    }
});