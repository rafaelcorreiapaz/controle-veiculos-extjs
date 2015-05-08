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
					id: 'tabAbastecimentos',
					store: 'abastecimento.store.Abastecimento',
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
									text: 'Emitir',
									iconCls: 'report',
									listeners:
									{
										click: 'onClickRelatorioAbastecimento'
									}
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
						{ text: 'Data', dataIndex: 'data', xtype:'datecolumn', format:'d/m/Y' },
						// { text: 'Histórico', dataIndex: 'historico', flex: 1 },
						{ text: 'Fornecedor', dataIndex: 'fornecedor', flex: 1 },
						{ text: 'Total', dataIndex: 'total' },
						{ text: 'Desconto', dataIndex: 'desconto' },
						{ text: 'Resultado', dataIndex: 'resultado' }
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
