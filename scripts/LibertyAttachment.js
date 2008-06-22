/* Dependencies: MochiKit Base Async, BitAjax.j  */
LibertyAttachment = {
	"uploader_under_way":0,
	
	"uploader": function(file, action, waitmsg, frmid, actionid) {
		if (LibertyAttachment.uploader_under_way) {
			alert(waitmsg);
		}else{
			var t = document.forms.editpageform.title.value;
			if ( MochiKit.Base.isEmpty(t) ){
				alert( "Please enter a title for your new content before attempting to upload a file." );
			}else{
				$('la_title').value = t;
				LibertyAttachment.uploader_under_way = 1;
				BitAjax.showSpinner();
				var old_target = file.form.target;
				file.form.target = frmid;
				var old_action = file.form.action;
				// var action_item = document.getElementById(actionid);
				// action_item.value = old_action;
				file.form.action=action;
				file.form.submit();
				file.form.target = old_target;
				file.form.action = old_action;
				// action_item.value = '';
			}
		}
	},

	"uploaderComplete": function(frmid, divid, fileid) {
		if (LibertyAttachment.uploader_under_way){
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

			var form = document.forms.editpageform;
			var cid = d.getElementById("upload_content_id").value;
			if ( typeof( form.content_id ) == "undefined" ){
				var i = INPUT( {'name':'content_id', 'type':'hidden', 'value':cid}, null );
				form.insertBefore( i, form.firstChild ); 
			}else{
				form.content_id.value = cid;
			}
			$('la_content_id').value = cid;

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
}
