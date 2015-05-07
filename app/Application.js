/**
 * The main application class. An instance of this class is created by app.js when it calls
 * Ext.application(). This is the ideal place to handle application launch and initialization
 * details.
 */
Ext.define('abastecimento.Application', {
    extend: 'Ext.app.Application',
    
    name: 'abastecimento',

    stores: [
    	'abastecimento.store.Abastecimento',
    	'abastecimento.store.AbastecimentoLocal',
    	'abastecimento.store.Veiculo',
    	'abastecimento.store.Fornecedor'
    ],
    
    launch: function () {
        // TODO - Launch the application
    }
});
