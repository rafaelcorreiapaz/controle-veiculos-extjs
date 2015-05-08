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

		var store = Ext.getCmp('gridAbastecimentos').getStore();

		var total = 0, desconto = 0;
		store.each(function(node){
			total += node.data.valor_total;
		});

		desconto = Ext.getCmp('numberFieldDesconto').getValue();
		total = total - desconto;

		Ext.getCmp('labelTotal').setText('<b>Total: R$ ' + total.toFixed(2) + '<b/>', false);
	},

	onKeyUpDesconto: function(number, e, eOpts)
	{

		var store = Ext.getCmp('gridAbastecimentos').getStore();

		var total = 0, desconto = 0;
		store.each(function(node){
			total += node.data.valor_total;
		});

		desconto = Ext.getCmp('numberFieldDesconto').getValue();
		total = total - desconto;

		Ext.getCmp('labelTotal').setText('<b>Total: R$ ' + total.toFixed(2) + '<b/>', false);

	},

	onClickRelatorioAbastecimento: function(btn, e, eOpts)
	{

		var modelSelected = Ext.getCmp('tabAbastecimentos').getSelectionModel();
		var record = modelSelected.getSelection()[0];
		if(record != undefined)
			window.open("php/view/Abastecimento/retornarAbastecimento?id=" + record.data.id);

	},	

});