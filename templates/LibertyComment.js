LibertyComment = {
	// constants
	'FORM_DIV_ID':'editcomments',
	'FORM_ID':'editcomment-form',
	'REPLY_ID':null,
	
	// functions
	'attachForm': function(elm, reply_id){
		LibertyComment.REPLY_ID = reply_id;
		LibertyComment.cancelComment();
		var form = $(LibertyComment.FORM_ID);
		if (form.parent_id === undefined){
			var idInput = MochiKit.DOM.INPUT({'type':'hidden', 'name':'parent_id', 'value':LibertyComment.ROOT_ID});
			form.appendChild( idInput );
		}
		if (form.parent_guid === undefined){
			var guidInput = MochiKit.DOM.INPUT({'type':'hidden', 'name':'parent_guid', 'value':LibertyComment.ROOT_GUID});
			form.appendChild( guidInput );
		}
		form.parent_id.value = LibertyComment.ROOT_ID;
		form.parent_guid.value = LibertyComment.ROOT_GUID;
		form.post_comment_reply_id.value = reply_id;
		form.post_comment_id.value = "";
		MochiKit.DOM.insertSiblingNodesAfter( $(elm), MochiKit.DOM.removeElement(LibertyComment.FORM_DIV_ID) );
		var form_div = $(LibertyComment.FORM_DIV_ID);
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
		$(LibertyComment.FORM_DIV_ID).style.display = "none";
		document.body.appendChild( MochiKit.DOM.removeElement(LibertyComment.FORM_DIV_ID) );	
	},
	'previewComment': function(){
		var f = MochiKit.DOM.formContents( $(LibertyComment.FORM_ID) );
		for (n in f[0]){
			if (f[0][n] == 'post_comment_submit'){ f[1][n] = null; }
		}
		//f.post_comment_preview.value = "Preview";
		//f.post_comment_submit.value = null;
		var str = bitRootUrl+"liberty/ajax_comments.php?" + queryString(f);
		MochiKit.Async.doSimpleXMLHttpRequest(str).addCallback(LibertyComment.displayPreview);
	},
	'postComment': function(){
		var f = MochiKit.DOM.formContents( $(LibertyComment.FORM_ID) );
		for (n in f[0]){
			if (f[0][n] == 'post_comment_preview'){ f[1][n] = null; }
		}
		var str = bitRootUrl+"liberty/ajax_comments.php?" + queryString(f);
		MochiKit.Async.doSimpleXMLHttpRequest(str).addCallback(LibertyComment.displayComment);
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
		preview.innerHTML = rslt.responseText;
		preview.style.marginLeft = (LibertyComment.REPLY_ID != null)?"20px":'0';				
		MochiKit.DOM.insertSiblingNodesBefore( $(LibertyComment.FORM_DIV_ID), preview);
		MochiKit.Visual.blindDown( preview, {afterFinish: function(){		
			if ( LibertyComment.BROWSER != "ie" ){
				MochiKit.Visual.ScrollTo( preview );
			}else{
				self.scrollTo( preview.offsetLeft, preview.offsetTop );
			}
		}});
	},
	'displayComment': function(rslt){
		var comment =  DIV(null, null);
		comment.innerHTML = rslt.responseText;
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

//			$(LibertyComment.FORM_DIV_ID).style.display = "none";
//			document.body.appendChild( MochiKit.DOM.removeElement(LibertyComment.FORM_DIV_ID) );
			
//			comment.style.visibility = 'visible';
			MochiKit.Visual.blindDown( comment, {afterFinish: function(){
				if ( LibertyComment.BROWSER != "ie" ){
					MochiKit.Visual.ScrollTo( comment );				
				}else{
					self.scrollTo( comment.offsetLeft, comment.offsetTop );
				}
			}});
		}});

	}
}
