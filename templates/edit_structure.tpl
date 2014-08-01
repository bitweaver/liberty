{strip}

{formfeedback hash=$feedback}

{if !$structureName}
	{assign var=structureName value="Structure"}
{/if}

<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/jquery-acisortable/js/jquery.aciPlugin.min.js"></script>
<script type="text/javascript" src="{$smarty.const.UTIL_PKG_URL}javascript/jquery-acisortable/js/jquery.aciSortable.js"></script>

{$structureToc}

<script type="text/javascript">
$.fn.aciSortable.defaults.container = 'ol';
$('#structure-{$structureTocId}').aciSortable( {literal}{
	child: 50,
	draggable: true,
	childHolder: '<ol class="structure-toc"></ol>',
	childHolderSelector: '.structure-toc',
	exclude: '.exclude',
	handle: '.structure-sort-handle',
	helper: '<div class="structure-sort-helper"></div>',
	helperSelector: '.structure-sort-helper',
	start: function(item, placeholder, helper) {
		// show item inside helper
		helper.css({
			opacity: 0.8
		}).html(item.html());
		// do not fadeIn helper if the item is from other sortable
		if (this.hasItem(item)) {
			helper.fadeIn();
		}
		item.slideUp();
	},
	end: function(item, hover, placeholder, helper) {
		if (placeholder.parent().length) {
			// add item after placeholder
			placeholder.after(item).detach();
		}
		item.slideDown();
		var top = $(window).scrollTop();
		var left = $(window).scrollLeft();
		var rect = item.get(0).getBoundingClientRect();
		// animate helper to item position
		helper.animate({
			top: rect.top + top,
			left: rect.left + left,
			opacity: 0
		},
		{
			complete: function() {
				// when completed detach the helper
				helper.detach();
			}
		});
	}
}{/literal} );

{literal}
// Changes XML to JSON
function structureTocToJson(xml) {
	if (xml.nodeName == 'LI') { // element
		if( xml.attributes.length == 0 ) {
			// do children
			if (xml.hasChildNodes()) {
				for(var i = 0; i < xml.childNodes.length; i++) {
					var childNode = xml.childNodes.item(i);
console.log( childNode );
					return structureTocToJson(childNode);
				}
			}
		} else {
			var obj = {};

			// do attributes
			if (xml.attributes.length > 0) {
				for (var j = 0; j < xml.attributes.length; j++) {
					var attribute = xml.attributes.item(j);
					if( attribute.nodeName == "structure_id" || attribute.nodeName == "content_id" ) {
						obj[attribute.nodeName] = attribute.value;
					}
				}
			}
			// do children
			if (xml.hasChildNodes()) {
				var children = null;
				childObjects = [];
				for(var i = 0; i < xml.childNodes.length; i++) {
					var childNode = xml.childNodes.item(i);
					if( childNode.nodeName == "OL" ) {
						children = structureTocToJson(childNode);
						if( children ) {
							obj["children"] = children;
						}
					}
				}
			}
			return obj;
		}
	} else if( xml.nodeName == 'OL' ) {
		var obj = [];
		// do children
		if (xml.hasChildNodes()) {
			for(var i = 0; i < xml.childNodes.length; i++) {
				var childNode = xml.childNodes.item(i);
				if( childNode.nodeName == "LI" ) {
					var children = structureTocToJson(childNode);
					if( children ) {
						obj.push(children);
					}
				}
			}
		}
		return obj;
	}
	return null;

};

function saveStructure( pStructureId ) {
	// var json = structureTocToJson(document.getElementById('structure-branch-'+pStructureId));$('#pre-tree').html(JSON.stringify(json,null,2)); 
	$.ajax({
		url: {/literal}"{$smarty.const.LIBERTY_PKG_URL}edit_structure_inc.php?tk={$gBitUser->mTicket}&submit_structure=1&structure_id="+pStructureId,{literal}
		type: 'POST',
		context: document.body,
		data: { 'structure_json': structureTocToJson(document.getElementById('structure-branch-'+pStructureId)) }
	})
	.done(function( response ) {
	  $("#structure-feedback").html( response );
	});
}

var undoHash = [];

function deleteStructureNode( pStructureId, pStructureText ) {
	if( confirm( "Are you sure you want to delete the following item, and all of its children? This cannot be undone."+"\n\nDELETE: "+pStructureText) ) {
		console.log( "deleted" );
		var eleId = '#structure-node-'+pStructureId;
		$(eleId).detach();
		// One day undoHash could be popped for undo
		undoHash.push( $(eleId) );
	}
}
{/literal}

</script>

		<div class="btn btn-primary" onclick="saveStructure('{$gStructure->mInfo.root_structure_id}');">{tr}Save Changes{/tr}</div> <a href="{$smart.const.BIT_ROOT_URL}index.php?structure_id={$gStructure->mInfo.root_structure_id}" class="btn btn-default"">{tr}Back{/tr}</a> <a class="btn btn-default" href="{$smarty.const.LIBERTY_PKG_URL}add_structure_content.php?structure_id={$smarty.request.structure_id}&amp;content_type_guid={$smarty.request.content_type_guid}" title="Add Content to {$gContent->getTitle()}" title="Add Content">Add Content</a>
<div id="structure-feedback">
</div>

{*<pre id="pre-tree"></pre>*}

{/strip}
