var DEBUGGING = 1;

// log to console
function log(txt) {
	if (DEBUGGING) {
		MyForm.app.log(txt);
		console.log(txt);
	}
}

function show_console() {
	if (DEBUGGING) {
		var console = Ext.getCmp("console")
		var w = Ext.getCmp("window");
		console.show();
		var wh = w.getHeight();
		w.setHeight(wh+console.getHeight());
		//Ext.getCmp("window").doLayout();
	}
}
