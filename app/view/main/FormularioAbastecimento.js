Ext.define('abastecimento.view.main.FormularioAbastecimento', {
	extend: 'Ext.window.Window',
	title: 'Formulário Abastecimento',
	xtype: 'formulario-data-movimentacao',
	id: 'FormularioAbastecimento',
	controller: 'main',
	height: 9*50,
	width: 16*50,
	maximizable: true,
	constrain: true,
	layout: 'fit',
	modal: true,
	items:
	[
		{
			xtype: 'form',
			border: false,
			bodyPadding: 10,
			url: 'php/view/Abastecimento/salvarAbastecimento',
			fieldDefaults:
			{
				labelAlign: 'top',
				labelWidth: 30,
				labelStyle: 'font-weight: bold;'
			},
			layout:
			{
				type: 'vbox',
				align: 'stretch'
			},
			items:
			[
				{
					xtype: 'fieldcontainer',
					layout: 'hbox',
					items:
					[
						{
							xtype: 'hiddenfield',
							name: 'id'
						},
						{
							xtype: 'datefield',
							name: 'data',
							fieldLabel: 'Data',
							format: 'd/m/Y',
							submitFormat: 'Y-m-d',
							allowBlank: false
						},
						{
							xtype: 'splitter'
						},
						{
							xtype: 'combobox',
							name: 'fornecedor',
							fieldLabel: 'Fornecedor',
							store: 'abastecimento.store.Fornecedor',
							displayField: 'nome',
							valueField: 'id',
							minChars: 0,
							allowBlank: false,
							flex: 1
						}
					]
				},
				{
					xtype: 'grid',
					title: 'Abastecimentos',
					id: 'gridAbastecimentos',
					store: 'abastecimento.store.AbastecimentoLocal',
					height: 280,
					border: true,
					selType: 'rowmodel',
					columns:
					[

						{
							text: 'Veículo',
							dataIndex: 'veiculo',
							flex: 1,
							editor:
							{
								xtype: 'combobox',
								store: 'abastecimento.store.Veiculo',
								displayField: 'placa',
								valueField: 'placa',
								minChars: 0,
							}
						},
						{
							text: 'Condutor',
							dataIndex: 'condutor',
							flex: 1,
							editor:
							{
								xtype: 'combobox',
								store: 'abastecimento.store.Fornecedor',
								displayField: 'nome',
								valueField: 'id',
								minChars: 0
							}
						},
						{
							text: 'KM',
							dataIndex: 'km',
							editor: 
							{
								xtype: 'numberfield',
								decimalPrecision: 3
							}
						},
						{
							text: 'Litros',
							dataIndex: 'litros',
							editor: 
							{
								xtype: 'numberfield',
								decimalPrecision: 3
							}
						},
						{
							text: 'Vlr Total',
							dataIndex: 'valor_total',
							flex: 1,
							minWidth: 120,
							maxWidth: 120,
							editor: 
							{
								xtype: 'numberfield',
								decimalPrecision: 3
							}
						}
					],
					bbar:
					[
						{
							text: 'Adicionar',
							iconCls: 'add',
							handler: function()
							{
								var rowEditing = this.up('grid').getPlugin('abastecimentoEdicaoLinha');
								rowEditing.cancelEdit();

								// var row = Ext.data.schema.Schema.lookupEntity('abastecimento.model.AbastecimentoLocal');
								var row = {};
								var store = Ext.getCmp('gridAbastecimentos').getStore();

								store.insert(0, row);
								rowEditing.startEdit(0, 0);
							}
						},
						{
							xtype: 'tbseparator'
						},
						{
							text: 'Deletar',
							iconCls: 'delete',
							handler: function()
							{
								var rowEditing = this.up('grid').getPlugin('abastecimentoEdicaoLinha');
								if(rowEditing.editing != true)
								{
									var store = Ext.getCmp('gridAbastecimentos').getStore();

									var modelSelected = Ext.getCmp('gridAbastecimentos').getSelectionModel();
									store.remove(modelSelected.getSelection());
								}
								else
								{
									Ext.Msg.show({
										title: 'Atenção!',
										msg: 'Você não pode excluir enquanto está adicionando/editando.',
										icon: Ext.Msg.WARNING
									});
								}
							}
						},
						{
							xtype: 'tbseparator'
						},
						{
							xtype: 'numberfield',
							id: 'numberFieldDesconto',
							name: 'desconto',
							width: 110,
							listeners: 
							{
								keyup: 'onKeyUpDesconto'
							}
						},
						'->',
						{
							xtype: 'label',
							id: 'labelTotal',
							width: 110
						}
					],
					plugins:
					{
						ptype: 'rowediting',
						autoCancel: false,
						editing: false,
						saveBtnText: "Salvar",
						cancelBtnText: "Cancelar",
						pluginId: 'abastecimentoEdicaoLinha',
						listeners:
						{
							validateedit: 'validarEdicaoAbastecimento',
							edit: 'afterValidacaoEdicaoAbastecimento'
						}
					}
				}
			],
			bbar:
			[
				'->',
				{
					align: 'right',
					text: 'Salvar',
					iconCls: 'disk',
					handler: function()
					{
						var form = this.up('form').getForm();
						if(form.isValid())
						{
							var array = [];
							var grid  = Ext.getCmp('gridAbastecimentos');
							var store = grid.getStore();
							var erro  = 0, estoque, quantidade;

							store.each(function(node){
								array.push(node.data);
							});

							stringDataStore = Ext.JSON.encode(array);

							form.submit({
								params: {
									lista: stringDataStore
								},
								success: function(form, action){

									Ext.Msg.alert('Sucesso', action.result.message);
									store.removeAll();
									store.sync();
									form.reset();

									var newStore = Ext.create('Ext.data.Store', {
										autoDestroy: true,
										model: 'abastecimento.model.AbastecimentoLocal'
									});

									var gridAbastecimentos = Ext.getCmp('tabAbastecimentos');
									gridAbastecimentos.getStore().reload();

								},
								failure: function(form, action) {
									Ext.Msg.alert('Falha', action.result.message);
								}
							});
						}
					}
				},
				{
					xtype: 'tbseparator'
				},
				{
					text: 'Fechar',
					iconCls: 'cancel',
					handler: function()
					{
						Ext.getCmp('FormularioAbastecimento').close();
					}
				}
			]

		}
	]
});