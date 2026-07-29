# Liberty source reference

> Generated from the current checkout and then intended for human review.
> Paths are relative to the package root.

## Inventory summary

| Artifact | Count |
|---|---:|
| PHP files | 126 |
| Smarty templates | 104 |
| JavaScript files | 2 |
| CSS files | 0 |

## Bootstrap and schema artifacts

- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `admin/upgrades/2.1.0.php`
- `admin/upgrades/2.1.1.php`
- `admin/upgrades/2.1.2.php`
- `admin/upgrades/2.1.3.php`
- `admin/upgrades/2.1.4.php`
- `admin/upgrades/3.0.0.php`
- `includes/bit_setup_inc.php`

## First-party classes and interfaces

- `includes/classes/LibertyBase.php:33` — `class LibertyBase extends BitBase {`
- `includes/classes/LibertyComment.php:23` — `class LibertyComment extends LibertyMime {`
- `includes/classes/LibertyContent.php:52` — `class LibertyContent extends LibertyBase implements BitCacheable {`
- `includes/classes/LibertyMime.php:26` — `class LibertyMime extends LibertyContent {`
- `includes/classes/LibertyStructure.php:19` — `class LibertyStructure extends LibertyBase {`
- `includes/classes/LibertySystem.php:71` — `class LibertySystem extends BitSingleton {`
- `plugins/filter.bitlinks.php:141` — `class BitLinks extends BitBase {`
- `plugins/filter.htmlpurifier.php:242` — `				class HTMLPurifier_AttrTransform_ForceValue extends HTMLPurifier_AttrTransform`
- `plugins/format.tikiwiki.php:63` — `class TikiWikiParser extends BitBase {`

## Web-facing PHP controllers

- `admin/action_logs.php`
- `admin/admin_liberty_inc.php`
- `admin/comments.php`
- `admin/plugins.php`
- `admin/schema_inc.php`
- `admin/upgrade_inc.php`
- `admin/upgrades/2.1.0.php`
- `admin/upgrades/2.1.1.php`
- `admin/upgrades/2.1.2.php`
- `admin/upgrades/2.1.3.php`
- `admin/upgrades/2.1.4.php`
- `admin/upgrades/3.0.0.php`
- `ajax_attachment_browser.php`
- `ajax_comments.php`
- `ajax_edit_storage.php`
- `attachment_browser.php`
- `attachment_uploader.php`
- `attachments.php`
- `content_permissions.php`
- `content_role_permissions.php`
- `download_file.php`
- `icons/index.php`
- `index.php`
- `liberty_rss.php`
- `list_content.php`
- `modules/mod_last_changes.php`
- `modules/mod_last_comments.php`
- `modules/mod_structure_navigation.php`
- `modules/mod_structure_toc.php`
- `modules/mod_top_authors.php`
- `preview.php`
- `redirect.php`
- `structure_add_content.php`
- `structure_edit.php`
- `view_file.php`

## Declared schema tables

- `liberty_action_log`
- `liberty_aliases`
- `liberty_attachment_meta_data`
- `liberty_attachment_prefs`
- `liberty_attachments`
- `liberty_comments`
- `liberty_content`
- `liberty_content_data`
- `liberty_content_history`
- `liberty_content_hits`
- `liberty_content_links`
- `liberty_content_permissions`
- `liberty_content_prefs`
- `liberty_content_status`
- `liberty_content_types`
- `liberty_copyrights`
- `liberty_dynamic_variables`
- `liberty_files`
- `liberty_link_cache`
- `liberty_meta_titles`
- `liberty_meta_types`
- `liberty_process_queue`
- `liberty_structures`

## Plugin and module directories

- `admin/plugins/`
- `modules/`
- `plugins/`
- `templates/plugins/`

## Templates

- `modules/help_mod_last_changes.tpl`
- `modules/help_mod_last_comments.tpl`
- `modules/help_mod_structure_navigation.tpl`
- `modules/help_mod_structure_toc.tpl`
- `modules/help_mod_top_authors.tpl`
- `modules/mod_last_changes.tpl`
- `modules/mod_last_comments.tpl`
- `modules/mod_structure_navigation.tpl`
- `modules/mod_structure_toc.tpl`
- `modules/mod_top_authors.tpl`
- `templates/action_logs.tpl`
- `templates/admin_comments.tpl`
- `templates/admin_liberty.tpl`
- `templates/admin_plugins.tpl`
- `templates/attachhelp.tpl`
- `templates/attachment_browser.tpl`
- `templates/attachment_browser_json.tpl`
- `templates/attachment_uploader.tpl`
- `templates/attachment_uploader_inc.tpl`
- `templates/attachments.tpl`
- `templates/center_list_generic.tpl`
- `templates/center_recent_comments.tpl`
- `templates/center_view_generic.tpl`
- `templates/comments.tpl`
- `templates/comments_display_option_bar.tpl`
- `templates/comments_post_inc.tpl`
- `templates/content_permissions.tpl`
- `templates/content_permissions_inc.tpl`
- `templates/content_role_permissions.tpl`
- `templates/content_role_permissions_inc.tpl`
- `templates/display_comment.tpl`
- `templates/display_content.tpl`
- `templates/edit_content_alias_inc.tpl`
- `templates/edit_content_owner_inc.tpl`
- `templates/edit_content_status_inc.tpl`
- `templates/edit_format.tpl`
- `templates/edit_help_inc.tpl`
- `templates/edit_primary_attachment_inc.tpl`
- `templates/edit_services_inc.tpl`
- `templates/edit_storage.tpl`
- `templates/edit_storage_list.tpl`
- `templates/edit_textarea.tpl`
- `templates/help_format_tikiwiki_inc.tpl`
- `templates/html_head_inc.tpl`
- `templates/libertypagination.tpl`
- `templates/list_comment_files_inc.tpl`
- `templates/list_content.tpl`
- `templates/list_content_inc.tpl`
- `templates/list_content_json.tpl`
- `templates/menu_liberty_admin.tpl`
- `templates/mime/application/view.tpl`
- `templates/mime/audio/admin.tpl`
- `templates/mime/audio/attachment.tpl`
- `templates/mime/audio/edit.tpl`
- `templates/mime/audio/inline.tpl`
- `templates/mime/audio/player.tpl`
- `templates/mime/audio/storage.tpl`
- `templates/mime/audio/view.tpl`
- `templates/mime/default/attachment.tpl`
- `templates/mime/default/inline.tpl`
- `templates/mime/default/storage.tpl`
- `templates/mime/default/upload.tpl`
- `templates/mime/default/view.tpl`
- `templates/mime/flash/inline.tpl`
- `templates/mime/flash/view.tpl`
- `templates/mime/image/admin.tpl`
- `templates/mime/image/attachment.tpl`
- `templates/mime/image/edit.tpl`
- `templates/mime/image/player.tpl`
- `templates/mime/image/view.tpl`
- `templates/mime/pbase/upload.tpl`
- `templates/mime/pdf/admin.tpl`
- `templates/mime/pdf/view.tpl`
- `templates/mime/video/admin.tpl`
- `templates/mime/video/attachment.tpl`
- `templates/mime/video/edit.tpl`
- `templates/mime/video/inline.tpl`
- `templates/mime/video/player.tpl`
- `templates/mime/video/storage.tpl`
- `templates/mime/video/view.tpl`
- `templates/mime_meta_inc.tpl`
- `templates/mime_view.tpl`
- `templates/plugins/data_code_admin.tpl`
- `templates/plugins/data_quote.tpl`
- `templates/plugins/data_toc.tpl`
- `templates/plugins/filter_htmlpurifier_admin.tpl`
- `templates/plugins/filter_simplepurifier_admin.tpl`
- `templates/rankings.tpl`
- `templates/service_content_body_inc.tpl`
- `templates/service_content_edit_mini_inc.tpl`
- `templates/service_content_edit_tab_inc.tpl`
- `templates/service_content_icon_inc.tpl`
- `templates/services_inc.tpl`
- `templates/storage_thumbs.tpl`
- `templates/structure_add_content.tpl`
- `templates/structure_add_feedback_inc.tpl`
- `templates/structure_display.tpl`
- `templates/structure_edit.tpl`
- `templates/structure_edit_content.tpl`
- `templates/structure_toc.tpl`
- `templates/structure_toc_endul.tpl`
- `templates/structure_toc_leaf.tpl`
- `templates/structure_toc_level.tpl`
- `templates/structure_toc_startul.tpl`

## Reading cautions

- Presence in this inventory does not make a file a supported public API.
- Bundled third-party libraries must be distinguished from package-owned code.
- Base schema files do not prove the migration state of a deployed database.
- Controllers may rely on include files, globals, services, and template callbacks not visible from their filename alone.
