Ext.define('abastecimento.model.Abastecimento', {
	extend: 'Ext.data.Model',
	fields:
	[
		{ name: 'id', type: 'int' },
		{ name: 'data', type: 'date' },
		// { name: 'historico', type: 'string' },
		{ name: 'fornecedor', type: 'string' },
		{ name: 'total', type: 'float' },
		{ name: 'desconto', type: 'float' },
		{ name: 'resultado', type: 'float' },
	]
});