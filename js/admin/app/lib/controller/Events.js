Ext.define('Indi.lib.controller.Events', {
    extend: 'Indi.lib.controller.Controller',
    actionsConfig: {
        index: {
            panelDockedInner$Actions$Agreement_InnerHandler: function(action, row, aix, btn) {
                this.panelDockedInner$Actions_DefaultInnerHandlerLoad(action, row, aix, btn);
            }
        }
    }
});