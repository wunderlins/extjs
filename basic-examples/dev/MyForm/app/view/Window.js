/*
 * File: app/view/Window.js
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

Ext.define('MyForm.view.Window', {
    extend: 'Ext.window.Window',

    autoRender: true,
    autoShow: true,
    height: 395,
    id: 'window',
    width: 458,
    layout: {
        type: 'border'
    },
    title: 'My Window',

    initComponent: function() {
        var me = this;

        Ext.applyIf(me, {
            items: [
                {
                    xtype: 'textareafield',
                    region: 'south',
                    split: true,
                    height: 150,
                    hidden: true,
                    id: 'console',
                    fieldStyle: 'font-family: courier, fixed; white-space: pre ! important;'
                },
                {
                    xtype: 'form',
                    flex: 1,
                    region: 'center',
                    id: 'form',
                    layout: {
                        align: 'stretch',
                        type: 'vbox'
                    },
                    bodyPadding: 10,
                    method: 'POST',
                    timeout: 15,
                    url: 'dump.php',
                    items: [
                        {
                            xtype: 'fieldcontainer',
                            flex: 1,
                            height: 120,
                            width: 400,
                            items: [
                                {
                                    xtype: 'textfield',
                                    formBind: true,
                                    fieldLabel: 'Name',
                                    name: 'name'
                                },
                                {
                                    xtype: 'combobox',
                                    formBind: true,
                                    fieldLabel: 'Language',
                                    name: 'lang'
                                },
                                {
                                    xtype: 'numberfield',
                                    formBind: true,
                                    fieldLabel: 'Age',
                                    name: 'age'
                                },
                                {
                                    xtype: 'htmleditor',
                                    formBind: true,
                                    height: 150,
                                    fieldLabel: 'Bio',
                                    name: 'bio',
                                    enableAlignments: false,
                                    enableFont: false,
                                    enableFontSize: false,
                                    enableSourceEdit: false
                                },
                                {
                                    xtype: 'datefield',
                                    formBind: true,
                                    fieldLabel: 'Birthdate',
                                    name: 'birthdate'
                                },
                                {
                                    xtype: 'radiogroup',
                                    width: 400,
                                    fieldLabel: 'Sex',
                                    items: [
                                        {
                                            xtype: 'radiofield',
                                            formBind: true,
                                            name: 'sex',
                                            boxLabel: 'Female',
                                            inputValue: '2'
                                        },
                                        {
                                            xtype: 'radiofield',
                                            formBind: true,
                                            name: 'sex',
                                            boxLabel: 'Male',
                                            inputValue: '1'
                                        },
                                        {
                                            xtype: 'radiofield',
                                            formBind: true,
                                            name: 'sex',
                                            boxLabel: 'Other',
                                            checked: true,
                                            inputValue: '0'
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            xtype: 'container',
                            layout: {
                                align: 'stretch',
                                pack: 'end',
                                type: 'hbox'
                            },
                            items: [
                                {
                                    xtype: 'button',
                                    width: 80,
                                    text: 'Cancel',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick1,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'button',
                                    margins: '0 0 0 5',
                                    width: 80,
                                    text: 'submit()',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick,
                                            scope: me
                                        }
                                    }
                                },
                                {
                                    xtype: 'button',
                                    margins: '0 0 0 10',
                                    text: 'Save',
                                    listeners: {
                                        click: {
                                            fn: me.onButtonClick2,
                                            scope: me
                                        }
                                    }
                                }
                            ]
                        }
                    ]
                }
            ]
        });

        me.callParent(arguments);
    },

    onButtonClick1: function(button, e, eOpts) {
        log("Cancel clicked");
    },

    onButtonClick: function(button, e, eOpts) {
        // submit data
        log("Submitting ...");
        Ext.getCmp("form").getForm().submit();


        // get submitted values
        log("fetching submitted data ...");
        Ext.Ajax.request({
            url: 'post.txt',
            success: function(conn, response, options) {
                log("loaded ...");
                log(conn.responseText);
            },

            failure: function(conn, response, options) {
                log("failed ...");
                log(conn.responseText);
            }
        });


    },

    onButtonClick2: function(button, e, eOpts) {
        with (Ext.getCmp("form").getForm()) {
            console.log(this);
        };

    }

});