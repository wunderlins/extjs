var DEBUGGING = 1;

// log to console
function log(txt) {
	if (DEBUGGING)
		MyForm.app.log(txt);
}

function show_console() {
	if (DEBUGGING) {
		Ext.getCmp("console").show();
		var wh = Ext.getCmp("window").getHeight();
		Ext.getCmp("window").setHeight(wh+Ext.getCmp("console").getHeight());
		//Ext.getCmp("window").doLayout();
	}
}
