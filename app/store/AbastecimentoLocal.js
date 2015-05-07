Ext.define('abastecimento.store.AbastecimentoLocal', {
	extend: 'Ext.data.Store',
	model: 'abastecimento.model.AbastecimentoLocal',
	proxy:
	{
		type: 'memory'
	}
});