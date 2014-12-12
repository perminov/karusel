Ext.define('Indi.controller.places', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$MaxChildrenCount: function(item) {
                return Ext.merge(item, {
                    minValue: 3
                })
            }
        }
    }
});