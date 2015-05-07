Ext.define('abastecimento.view.main.MainController', {
    extend: 'Ext.app.ViewController',

    requires: [
        'Ext.window.MessageBox',
        'abastecimento.view.main.FormularioAbastecimento',
    ],

    alias: 'controller.main',

    validarEdicaoAbastecimento: function(editor, context, eOpts)
    {
        editor.editing = true;
    },

    afterValidacaoEdicaoAbastecimento: function(editor, context, eOpts)
    {

    }

});