/*
 * File: app/view/MainView.js
 *
 * This file was generated by Sencha Architect version 3.0.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Sencha Touch 2.3.x library, under independent license.
 * License of Sencha Architect does not include license for Sencha Touch 2.3.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('TouchLogin.view.MainView', {
    extend: 'Ext.navigation.View',
    alias: 'widget.mainview',

    config: {
        items: [
            {
                xtype: 'panel',
                itemId: 'homePanel',
                layout: {
                    type: 'fit'
                },
                items: [
                    {
                        xtype: 'panel',
                        itemId: 'loginPanel',
                        items: [
                            {
                                xtype: 'button',
                                itemId: 'showLoginButton',
                                margin: 20,
                                padding: 8,
                                text: 'Login'
                            },
                            {
                                xtype: 'button',
                                itemId: 'showRegisterButton',
                                margin: 20,
                                padding: 8,
                                text: 'Register'
                            }
                        ]
                    },
                    {
                        xtype: 'panel',
                        hidden: true,
                        itemId: 'welcomePanel',
                        items: [
                            {
                                xtype: 'label',
                                centered: true,
                                html: 'Welcome!',
                                itemId: 'welcomeLabel'
                            }
                        ]
                    }
                ]
            }
        ]
    }

});