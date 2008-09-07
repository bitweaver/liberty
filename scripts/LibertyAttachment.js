/* Dependencies: MochiKit Base Async, BitAjax.j  */
LibertyAttachment = {
	"fileInputClones":{},
	"uploader_under_way":0,

	"uploaderSetup":function(fileid){
		LibertyAttachment.fileInputClones[fileid] = $(fileid).cloneNode(true);
	},
	
	"uploader": function(file, action, waitmsg, frmid, cformid ) {
		if (LibertyAttachment.uploader_under_way) {
			alert(waitmsg);
		}else{
			if ( LibertyAttachment.preflightCheck( cformid ) ){
				LibertyAttachment.uploader_under_way = 1;
				BitBase.showSpinner();
				var old_target = file.form.target;
				file.form.target = frmid;
				var old_action = file.form.action;
				file.form.action=action;
				file.form.submit();
				file.form.target = old_target;
				file.form.action = old_action;
			}else{
				var fileid = file.id;
				LibertyAttachment.fileInputClones[fileid].id = fileid;
				MochiKit.DOM.swapDOM(file, LibertyAttachment.fileInputClones[fileid]);
				LibertyAttachment.uploaderSetup( fileid );
			}
		}
	},

	"preflightCheck": function( cformid ){
		var f = $(cformid);
		var t = f.title.value;
		if ( MochiKit.Base.isEmpty(t) ){
			alert( "Please enter a title for your new content before attempting to upload a file." );
			return false;
		}else{
			f['liberty_attachments[title]'].value = t;
			return true;
		}
	},

	"uploaderComplete": function(frmid, divid, fileid, cformid) {
		if (LibertyAttachment.uploader_under_way){
			BitBase.hideSpinner();
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
			
			LibertyAttachment.postflightCheck( cformid, d );

			var errMsg = "<div>Sorry, there was a problem retrieving results.</div>";
			var divO = document.getElementById(divid);
			divR = d.getElementById('result_tab');
			if (divO != null) {
				divO.innerHTML = (divR != null)?divR.innerHTML:errMsg+"a";
			}
			divid = divid + '_tab';
			divO = document.getElementById(divid);
			var divR = d.getElementById('result_list');
			if (divO != null) {
				divO.innerHTML =  (divR != null)?divR.innerHTML:errMsg+"b";
			}
			LibertyAttachment.uploader_under_way = 0;
			var file = document.getElementById(fileid);
			LibertyAttachment.fileInputClones[fileid].id = fileid;
			MochiKit.DOM.swapDOM(file, LibertyAttachment.fileInputClones[fileid]);
			LibertyAttachment.uploaderSetup( fileid );
			// file.value = '';
		}
	},
	
	"postflightCheck": function( cformid, d ){
		var form = $(cformid);
		var cid = d.getElementById("upload_content_id").value;
		if ( typeof( form.content_id ) == "undefined" ){
			var i = INPUT( {'name':'content_id', 'type':'hidden', 'value':cid}, null );
			form.insertBefore( i, form.firstChild ); 
		}else{
			form.content_id.value = cid;
		}
		form['liberty_attachments[content_id]'].value = cid;
	}
}
