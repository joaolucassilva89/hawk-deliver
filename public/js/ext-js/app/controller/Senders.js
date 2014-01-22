Ext.define('App.controller.Senders', {
    extend: 'Ext.app.Controller',
    create: function() {
        var SendersCreateForm = Ext.widget('form', {
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            border: false,
            bodyPadding: 10,
            fieldDefaults: {
                labelAlign: 'top',
                labelStyle: 'font-weight:bold',
                labelSeparator: ':',
                msgTarget: 'under'
            },
            url: '/senders/create',
            waitMsgTarget: true,
            items: [
                {
                    xtype: 'textfield',
                    fieldLabel: 'Nome',
                    name: 'name'
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Endereço de e-mail',
                    name: 'email'
                }
            ],
            buttons: [
                {
                    text: 'Cancelar',
                    handler: function() {
                        win.destroy();
                    }
                },
                {
                    text: 'Criar',
                    handler: function() {
                        SendersCreateForm.submit({
                            clientValidation: false,
                            submitEmptyText: false,
                            waitMsg: 'Aguarde...',
                            success: function(form, action) {
                                Ext.MessageBox.alert('Sucesso', 'O remetente foi adicionado', function() {
                                    SendersGrid.getStore().load();
                                    win.destroy();
                                });
                            },
                            failure: function(form, action) {
                            }
                        });
                    }
                }]
        });

        win = Ext.widget('window', {
            title: 'Adicionar remetente',
            closeAction: 'hide',
            width: 400,
            minWidth: 300,
            layout: 'fit',
            resizable: false,
            modal: true,
            items: SendersCreateForm,
            defaultFocus: 'name',
            close: function() {
                win.destroy();
            }
        });
        win.show();
    }
});