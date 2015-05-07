Ext.define('abastecimento.store.Fornecedor', {
	extend: 'Ext.data.Store',
	model: 'abastecimento.model.Fornecedor',
	pageSize: 30,
	proxy:
	{
		type: 'ajax',
		url: 'php/view/Fornecedor/retornarFornecedoresJSON',
		reader:
		{
			type: 'json',
			rootProperty: 'fornecedores',
			totalProperty: 'total'
		}
	}
});