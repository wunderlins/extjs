/*
 * File: app/controller/MyController.js
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

Ext.define('MyForm.controller.MyController', {
    extend: 'Ext.app.Controller',

    onLaunch: function() {
        var defaultRecord = Ext.create('MyForm.model.MyModel', {
            id   : 1,
            name : "Simon Wunderlin",
            age  : 39,
            lang : "Eng",
            bio  : "Blah ...",
            birthdate: "03.12.1974",
            sex: 1
        });

        Ext.getCmp("form").loadRecord(defaultRecord);
        console.log("loaded default record");
        console.log(defaultRecord);

    }

});