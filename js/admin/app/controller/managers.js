Ext.define('Indi.controller.managers', {
    extend: 'Indi.Controller',
    actionsConfig: {
        form: {
            formItem$Email: {fieldLabel: 'Логин'},
            formItem$Password: {minLength: 7}
        }
    }
});