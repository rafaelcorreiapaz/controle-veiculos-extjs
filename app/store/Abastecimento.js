Ext.define('abastecimento.store.Abastecimento', {
	extend: 'Ext.data.Store',
	model: 'abastecimento.model.Abastecimento',
	pageSize: 100,
	autoLoad: true,
	remoteSort: true,
	remoteFilter: true,
	sorters:
	[
		{
			property: 'id',  direction: 'DESC'
		}
	],
	proxy:
	{
		type: 'ajax',
		url: 'php/view/Abastecimento/retornarAbastecimentosJSON',
		reader:
		{
			type: 'json',
			rootProperty: 'abastecimentos',
			totalProperty: 'total'
		}
	}
});