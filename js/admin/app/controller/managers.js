Ext.define('Indi.controller.managers', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Password: {minLength: 7}
        }
    }
});