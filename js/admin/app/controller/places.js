Ext.define('Indi.controller.places', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$MaxChildrenCount: {
                minValue: 3
            }
        }
    }
});