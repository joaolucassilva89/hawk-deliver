Ext.define('App.controller.Account', {
    extend: 'Ext.app.Controller',
    password: function() {
        var AccountPasswordForm = Ext.widget('form', {
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
            url: '/account/password',
            waitMsgTarget: true,
            items: [
                {
                    xtype: 'textfield',
                    fieldLabel: 'Palavra-passe atual',
                    inputType: 'password',
                    name: 'password'
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Nova palavra-passe',
                    inputType: 'password',
                    name: 'new-password'
                },
                {
                    xtype: 'textfield',
                    fieldLabel: 'Confirmar nova palavra-passe',
                    inputType: 'password',
                    name: 'confirm-new-password'
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
                    text: 'Alterar',
                    handler: function() {
                        AccountPasswordForm.submit({
                            clientValidation: false,
                            submitEmptyText: false,
                            waitMsg: 'Aguarde...',
                            success: function(form, action) {
                                Ext.MessageBox.alert('Sucesso', 'A palavra-passe foi alterada', function() {
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
            title: 'Alterar palavra-passe',
            closeAction: 'hide',
            width: 400,
            minWidth: 300,
            layout: 'fit',
            resizable: false,
            modal: true,
            items: AccountPasswordForm,
            defaultFocus: 'password',
            close: function() {
                win.destroy();
            }
        });
        win.show();
    }
});