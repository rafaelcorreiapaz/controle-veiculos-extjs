Ext.define('abastecimento.store.Veiculo', {
	extend: 'Ext.data.Store',
	model: 'abastecimento.model.Veiculo',
	pageSize: 0,
	autoLoad: true,
	proxy:
	{
		type: 'ajax',
		url: 'php/view/Veiculo/retornarVeiculosJSON',
		reader:
		{
			type: 'json',
			rootProperty: 'veiculos',
			totalProperty: 'total'
		}
	}
});