Ext.define('abastecimento.view.main.Main', {
	extend: 'Ext.container.Container',
	requires: [
		'abastecimento.view.main.MainController',
		'abastecimento.view.main.MainModel'
	],

	xtype: 'app-main',
	
	controller: 'main',
	viewModel: {
		type: 'main'
	},
	layout: {
		type: 'border'
	},
	items:
	[
		{
			region: 'center',
			xtype: 'tabpanel',
			margin: '5 5 5 5',
			items:
			[
				{
					title: 'Abastecimentos',
					xtype: 'gridpanel',
					dockedItems:
					[
						{
							xtype: 'toolbar',
							dock: 'top',
							items:
							[
								{
									text: 'Adicionar',
									iconCls: 'add',
									handler: function(){
										Ext.create('abastecimento.view.main.FormularioAbastecimento').show();										
									}
								},
								{
									text: 'Deletar',
									iconCls: 'delete'
								},
								{
									text: 'Transmitir',
									iconCls: 'transmit'
								},
							]
						}
					],
					columns:
					[
						{ text: 'Id', dataIndex: 'id' },
						{ text: 'Data', dataIndex: 'data' },
						{ text: 'Histórico', dataIndex: 'historico', flex: 1 },
						{ text: 'Fornecedor', dataIndex: 'fornecedor', flex: 1 },
						{ text: 'Total', dataIndex: 'total' },
						{ text: 'Desconto', dataIndex: 'desconto' }
					],

				},
/*				{
					title: 'Manutenções'
				},
				{
					title: 'Condutores'
				},
				{
					title: 'Veículos'
				},
				{
					title: 'Peças'
				},
				{
					title: 'Fornecedores'
				},
*/			]
		}
	]
});
