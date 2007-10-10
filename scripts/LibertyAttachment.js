/* Dependencies: ajax.js, MochiKit Base Async */
LibertyAttachment = {
	"uploader_under_way":0,
	
	"uploader": function(file, action, waitmsg, frmid) {
		if (LibertyAttachment.uploader_under_way) {
			alert(waitmsg);
		}
		else {
			LibertyAttachment.uploader_under_way = 1;
			BitAjax.showSpinner();
			var old_target = file.form.target;
			file.form.target = frmid;
			var old_action = file.form.action;
			file.form.action=action;
			file.form.submit();
			file.form.target = old_target;
			file.form.action = old_action;
		}
	},

	"uploaderComplete": function(frmid, divid, fileid) {
		BitAjax.hideSpinner();
		var ifrm = document.getElementById(frmid);
		if (ifrm.contentDocument) {
			var d = ifrm.contentDocument;
		} else if (ifrm.contentWindow) {
			var d = ifrm.contentWindow.document;
		} else {
			var d = window.frames[frmid].document;
		}
		if (d.location.href == "about:blank") {
			return;
		}
		var errMsg = "<div>Sorry, there was a problem retrieving results.</div>";
		var divO = document.getElementById(divid);		
		divR = d.getElementById('result_tab');
		if (divO != null) {
			divO.innerHTML =  (divR != null)?divR.innerHTML:errMsg+"a";
		}
		divid = divid + '_tab';
		divO = document.getElementById(divid);
		var divR = d.getElementById('result_list');
		if (divO != null) {
			divO.innerHTML =  (divR != null)?divR.innerHTML:errMsg+"b";
		}
		LibertyAttachment.uploader_under_way = 0;
		var file = document.getElementById(fileid);
		file.value = '';
	}
}