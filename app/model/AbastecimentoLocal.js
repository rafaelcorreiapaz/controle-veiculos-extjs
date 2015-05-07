Ext.define('abastecimento.model.AbastecimentoLocal', {
	extend: 'Ext.data.Model',
	fields:
	[
		{ name: 'veiculo', type: 'string' },
		{ name: 'condutor', type: 'int' },
		{ name: 'km', type: 'float' },
		{ name: 'litros', type: 'float' },
		{ name: 'valor_total', type: 'float' }
	]
});