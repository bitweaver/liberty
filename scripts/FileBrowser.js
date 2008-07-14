// We use Mochikit library for AJAX
FileBrowser = {
	"load": function( configName ) {
		if( configName ) {
			BitAjax.showSpinner();
			doSimpleXMLHttpRequest( "/liberty/ajax_file_browser.php", merge( {ajax_path_conf:configName} )).addCallback( this.browseCallback, "ajax_load" );
			document.getElementById( "ajax_load_title" ).innerHTML = '';
			BitAjax.hideSpinner();
		}
	},

	"browse": function( relPath, state, configName ) {
		if( relPath ) {
			BitAjax.showSpinner();
			if( state == 'close' ) {
				document.getElementById( relPath ).title = "open";
				document.getElementById( relPath+"-bitInsert" ).innerHTML = '';
				if( document.getElementById( "image-"+relPath )) {
					document.getElementById( "image-"+relPath ).src = bitIconStyleDir+"small/folder.png";
				}
			} else {
				document.getElementById(relPath).title = "close";
				if( document.getElementById( "image-"+relPath )) {
					document.getElementById( "image-"+relPath ).src = bitIconStyleDir+"small/folder-open.png";
				}
				doSimpleXMLHttpRequest( "/liberty/ajax_file_browser.php", merge( {relpath:relPath,ajax_path_conf:configName} )).addCallback( this.browseCallback, relPath+"-bitInsert" );
			}
			BitAjax.hideSpinner();
		}
	},

	"browseCallback": function( insertID, result ) {
		document.getElementById( insertID ).innerHTML = result.responseText;
	}
}
