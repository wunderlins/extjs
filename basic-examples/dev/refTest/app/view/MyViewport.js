/*
 * File: app/view/MyViewport.js
 *
 * This file was generated by Sencha Architect version 3.0.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Ext JS 4.2.x library, under independent license.
 * License of Sencha Architect does not include license for Ext JS 4.2.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('refTest.view.MyViewport', {
    extend: 'Ext.container.Viewport',

    layout: {
        type: 'fit'
    },

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'form',
                    itemId: 'FirstForm',
                    bodyPadding: 10,
                    title: 'My Form',
                    items: [
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            fieldLabel: 'Name'
                        },
                        {
                            xtype: 'textfield',
                            anchor: '100%',
                            fieldLabel: 'Vorname'
                        },
                        {
                            xtype: 'combobox',
                            anchor: '100%',
                            fieldLabel: 'Land'
                        },
                        {
                            xtype: 'filefield',
                            anchor: '100%',
                            fieldLabel: 'File'
                        },
                        {
                            xtype: 'htmleditor',
                            anchor: '100%',
                            height: 150,
                            fieldLabel: 'Kommentar'
                        },
                        {
                            xtype: 'container',
                            layout: {
                                align: 'stretch',
                                defaultMargins: '{"top":0,"right":0,"bottom":0,"left":5}',
                                pack: 'end',
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    text: 'Cancel'
                                },
                                {
                                    xtype: 'button',
                                    itemId: 'btnSave',
                                    text: 'Save'
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    }

});