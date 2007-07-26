LibertyComment = {
	// constants
	'FORM_DIV_ID':'edit_comments',
	'FORM_ID':'editcomment-form',
	'REPLY_ID':null,
	
	// functions
	'attachForm': function(elm, reply_id, root_id){
		LibertyComment.REPLY_ID = reply_id;
		LibertyComment.cancelComment();
		var form_div = MochiKit.DOM.removeElement( LibertyComment.FORM_DIV_ID );
		MochiKit.DOM.insertSiblingNodesAfter( $(elm), form_div );

		var form = $(LibertyComment.FORM_ID);
		if (form.parent_id === undefined){
			var idInput = MochiKit.DOM.INPUT({'type':'hidden', 'name':'parent_id', 'value':root_id});
			form.appendChild( idInput );
		}
		form.parent_id.value = LibertyComment.ROOT_ID = root_id;
		form.post_comment_reply_id.value = reply_id;
		form.post_comment_id.value = "";
		
		form_div.style.display = "block";
		if ( LibertyComment.BROWSER != "ie" ){
			MochiKit.Visual.ScrollTo( form_div );
		}else{
			self.scrollTo( form_div.offsetLeft, form_div.offsetTop );
		}
	},
	'resetForm': function(){
		$(LibertyComment.FORM_ID).reset();
	},
	'detachForm': function(){
		var form_div = $(LibertyComment.FORM_DIV_ID);
		form_div.style.display = "none";
		document.body.appendChild( form_div );
	},
	'previewComment': function(){
		var f = MochiKit.DOM.formContents( $(LibertyComment.FORM_ID) );
		for (n in f[0]){
			if (f[0][n] == 'post_comment_submit' || f[0][n] == 'post_comment_cancel'){ f[1][n] = null; }
		}
		var url = bitRootUrl+"liberty/ajax_comments.php";
		var data = queryString(f);		
		var req = getXMLHttpRequest();
		req.open('POST', url, true);
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.setRequestHeader('Content-Length',data.length);
		var post = sendXMLHttpRequest(req,data);
		post.addCallbacks(LibertyComment.displayPreview); 
	},
	'postComment': function(){
		var f = MochiKit.DOM.formContents( $(LibertyComment.FORM_ID) );
		for (n in f[0]){
			if (f[0][n] == 'post_comment_preview' || f[0][n] == 'post_comment_cancel'){ f[1][n] = null; }
		}
		var url = bitRootUrl+"liberty/ajax_comments.php";
		var data = queryString(f);		
		var req = getXMLHttpRequest();
		req.open('POST', url, true);
		req.setRequestHeader('Content-type','application/x-www-form-urlencoded');
		req.setRequestHeader('Content-Length',data.length);
		var post = sendXMLHttpRequest(req,data);
		post.addCallbacks(LibertyComment.checkRslt); 
	},
	'cancelComment': function(ani){
		LibertyComment.cancelPreview(true);
		if (ani == true){
			MochiKit.Visual.blindUp( LibertyComment.FORM_DIV_ID, {afterFinish: function(){
				LibertyComment.detachForm();
				LibertyComment.resetForm();
				
				var reply_div = $('comment_'+LibertyComment.REPLY_ID);
				if ( LibertyComment.BROWSER != "ie" ){
					MochiKit.Visual.ScrollTo( reply_div );
				}else{
					self.scrollTo( reply_div.offsetLeft, reply_div.offsetTop );
				}
			}});
		}else{
			LibertyComment.detachForm();
			LibertyComment.resetForm();
		}
	},
	'cancelPreview': function(ani){
		if ( $('comment_preview') != null){
			if (ani == true){
				MochiKit.Visual.blindUp( $('comment_preview'), {afterFinish: function(){
					MochiKit.DOM.removeElement( $('comment_preview') );
				}});
			}else{
				return	MochiKit.DOM.removeElement( $('comment_preview') );
			}
		}
	},
	'displayPreview': function(rslt){
		if ( $('comment_preview') == null){
			var preview = DIV({'id':'comment_preview'}, null);		
		}else{
			var preview = LibertyComment.cancelPreview();
		}
		preview.style.display = 'none';
		var xml = rslt.responseXML;
		preview.innerHTML = xml.documentElement.getElementsByTagName('content')[0].firstChild.nodeValue;
		preview.style.marginLeft = (LibertyComment.REPLY_ID != LibertyComment.ROOT_ID)?"20px":'0';				
		MochiKit.DOM.insertSiblingNodesBefore( $(LibertyComment.FORM_DIV_ID), preview);
		MochiKit.Visual.blindDown( preview, {afterFinish: function(){		
			if ( LibertyComment.BROWSER != "ie" ){
				MochiKit.Visual.ScrollTo( preview );
			}else{
				//self.scrollTo( preview.offsetLeft, preview.offsetTop );
			}
		}});
	},
	'checkRslt': function(rslt){
		var xml = rslt.responseXML;
		var status = xml.documentElement.getElementsByTagName('code')[0].firstChild.nodeValue;
		if (status == '200'){
			LibertyComment.displayComment(rslt);
		}else{
			//if status is 400, 401, or 405 still call preview - allowing someone to save their typed text.
			LibertyComment.displayPreview(rslt);
		}
	},
	'displayComment': function(rslt){
		var xml = rslt.responseXML;
		var comment =  DIV(null, null);
		comment.innerHTML = xml.documentElement.getElementsByTagName('content')[0].firstChild.nodeValue;
		comment.style.marginLeft = (LibertyComment.REPLY_ID != LibertyComment.ROOT_ID)?"20px":'0';
		comment.style.display = 'none';
		if (LibertyComment.SORT_MODE == "commentDate_asc"){
			MochiKit.DOM.insertSiblingNodesBefore( $('comment_'+LibertyComment.REPLY_ID+'_footer'), comment );
		}else{
			MochiKit.DOM.insertSiblingNodesAfter( $('comment_'+LibertyComment.REPLY_ID), comment );
		}

		LibertyComment.cancelPreview( true );
		MochiKit.Visual.blindUp( LibertyComment.FORM_DIV_ID, {afterFinish: function(){
			LibertyComment.detachForm();
			LibertyComment.resetForm();
			MochiKit.Visual.blindDown( comment, {afterFinish: function(){
				if ( LibertyComment.BROWSER != "ie" ){
					MochiKit.Visual.ScrollTo( comment );				
				}else{
					//self.scrollTo( comment.offsetLeft, comment.offsetTop );
				}
			}});
		}});
	}
}
