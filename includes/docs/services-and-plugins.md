# Liberty services and plugins

Liberty has two complementary extension systems:

- **Services** let packages attach behavior to content lifecycle operations.
- **Plugins** parse, filter, store, inspect, or render content and files.

They are registries, not arbitrary include conventions. Extensions must use
stable GUIDs and documented callback contracts.

## Liberty services

`LibertySystem::registerService()` associates a service name, provider package,
callback/template map, and options. `LibertyContent::invokeServices()` calls
registered callbacks at lifecycle points.

Core service GUID constants include:

- `LIBERTY_SERVICE_ACCESS_CONTROL`
- `LIBERTY_SERVICE_CATEGORIZATION`
- `LIBERTY_SERVICE_COMMERCE`
- `LIBERTY_SERVICE_CONTENT_TEMPLATES`
- `LIBERTY_SERVICE_DOCUMENT_GENERATION`
- `LIBERTY_SERVICE_FORUMS`
- `LIBERTY_SERVICE_GROUP`
- `LIBERTY_SERVICE_MAPS`
- `LIBERTY_SERVICE_METADATA`
- `LIBERTY_SERVICE_MENU`
- `LIBERTY_SERVICE_RATING`
- `LIBERTY_SERVICE_SEARCH`
- `LIBERTY_SERVICE_THEMES`
- `LIBERTY_SERVICE_TRANSLATION`
- `LIBERTY_SERVICE_TRANSLITERATION`
- `LIBERTY_SERVICE_LIBERTYSECURE`
- `LIBERTY_SERVICE_MODCOMMENTS`
- `LIBERTY_SERVICE_UPLOAD`

The constant is an integration slot, not proof that a provider is installed.
Use `hasService()` and required-service metadata appropriately.

### Callback categories

Service maps can contribute:

- Load/store/update/expunge behavior.
- SQL fragments for loading and listing.
- Edit validation and preview behavior.
- Display callbacks.
- Edit tabs, mini-edit fragments, icons, and content-body templates.

Callbacks commonly receive the content object and a parameter hash by
reference. They can add errors, data, SQL fragments, or presentation state.
Changing a shared parameter key is an API change across packages.

### Template locations

A template location is a place in a page where every registered service may
add markup. There is no registry of locations. A content template names one:

```smarty
{include file="bitpackage:liberty/services_inc.tpl" serviceLocation='icon' serviceHash=$gContent->mInfo}
```

`services_inc.tpl` builds the key `content_<location>_tpl`, asks
`LibertySystem::getServiceValues()` for every service that registered that key,
and includes each template in registration order with `$serviceHash`. A
provider claims a location by registering the key:

```php
$gLibertySystem->registerService( MY_SERVICE_GUID, MY_PKG_NAME, array(
	'content_icon_tpl' => 'bitpackage:mypkg/service_icon_inc.tpl',
) );
```

Display locations in use:

| Location | Key | Where content templates put it | Meant for |
|---|---|---|---|
| `icon` | `content_icon_tpl` | The row of small tools on a content page | One quiet icon or icon-and-label link per provider |
| `action` | `content_action_tpl` | Beside the title, with the page's own buttons | A main action another package offers for this content, as one button |
| `nav` | `content_nav_tpl` | Under the title, above the content | Navigation between related content |
| `body` | `content_body_tpl` | With the content itself; also per row in some lists | Markup that belongs to the content |
| `view` | `content_view_tpl` | After the content | Blocks that follow it, such as comments or related items |
| `wrapper` | `content_wrapper_tpl` | `kernel/templates/html.tpl`, at the top of the centre column | Page-level markup above any content |
| `row` | `content_row_tpl` | `kernel/templates/html.tpl`, before the layout columns | Page-level markup across the full width |
| `comment` | `content_comment_tpl` | Each displayed comment | Per-comment tools |
| `list` | `content_list_tpl` | Each row of a generic content list | Per-row markup |
| `list_sort`, `list_options`, `list_actions` | `content_list_sort_tpl`, … | List headers and per-row action cells | Sort links, list options, row actions |

Rules that follow from how `services_inc.tpl` works:

- **Every provider's template is included on every page that offers the
  location**, whatever the content type and whether or not the content uses
  that service. The template must test what it was given (content type,
  permission) and print nothing otherwise.
- Only `nav` and `view` are wrapped, in `<div class="services-nav">` or
  `<div class="services-view">`, and only when some template printed
  something. The other locations are output bare, so the provider's markup
  must fit the surrounding element (for `icon`, an inline link).
- `$serviceHash` is the content's info hash on a content page and the row hash
  in a list. `$gContent` is whatever the page assigned and may be absent in a
  list.
- A location nobody registered costs one lookup and prints nothing, so a
  content template may offer a location before any provider exists.

Edit and upload forms use a second include, `edit_services_inc.tpl`, with the
key passed whole (`serviceFile="content_edit_mini_tpl"`). Unlike the display
locations it skips a provider when the content does not use that service
(`$gContent->hasService()`). Keys in use: `content_edit_mini_tpl`,
`content_edit_tab_tpl`, `content_upload_mini_tpl`, `content_upload_tab_tpl`,
`content_upload_form_tpl` and `content_upload_js_tpl`.

Adding a location is a change to the content template only: choose a name that
says what belongs there, include `services_inc.tpl` with it, and record it in
the content package's documentation and in the table above. Do not reuse a
location for markup it was not meant for; a button is not navigation.

### Services are not a command bus

A service GUID is a **lifecycle and presentation slot**, not a general RPC
surface. `invokeServices()` runs during load, store, update, expunge, list SQL,
and display/edit template collection. It is the wrong place to put an
on-demand operation such as “transform this file now”, a paid remote API call,
or a long-running job.

Put those operations in the **provider package**: a PHP class plus a
controller (or CLI) that the caller invokes directly. Other packages should
call that class, or gate UI with `isPackageActive()` / `hasService()`. Do not
invent a service callback whose only job is to run a command.

Processor plugins (`liberty_get_function('resize')` and similar) are **local**
image and media operations through GD, ImageMagick, or MagickWand. They take a
file hash and return a path. They are not a home for network APIs.

Optional packages that must not be hard dependencies of a public content type
(for example an install-only feature on `fisheyeimage`) can still register
`content_icon_tpl` or `content_edit_mini_tpl`. The content package already
invokes those template slots; the **handler URL** stays in the provider
package. Test with the provider **absent**. Do not mark an install-only
service required on a public content type.

Choose an existing GUID only when the semantic slot matches. A new GUID is
justified for a new lifecycle concern (access control, upload, translation),
not for one command.

### SQL service hooks

`getLibertySql()`, `getServicesSql2()`, and `convertQueryHash()` compose service
query contributions. A hook must:

- Alias columns to avoid collisions.
- Use bind variables.
- Avoid changing row cardinality unintentionally.
- Support the count-query form.
- Apply the same access policy to list and object-load paths.

## Plugin discovery

At package setup, Liberty loads active plugins or scans all plugin directories
when configuration is absent/stale. `scanAllPlugins()` discovers Liberty's own
`plugins/` plus package-provided `liberty_plugins/` directories after packages
have been registered.

Plugin status is stored in Kernel configuration. An installed file is not
necessarily active.

## Plugin categories

### Format

Format plugins define how a content body's source representation is verified,
stored, and parsed. Current files include BBCode, BitHTML, Markdown (Parsedown
in Util `includes/parsedown/`), PEAR Wiki, simple text, and TikiWiki handlers.

Standard callback concepts are:

- Verify source before storage.
- Normalize/save source.
- Parse source for display.

The precise function names are registered in each plugin's parameter hash.

### Data

Data plugins expand embedded tags such as code, images, attachments, includes,
tables of contents, video, and structured presentation helpers. They execute
during parsing and must treat both tag parameters and retrieved content as
untrusted.

Plugin help/argument metadata is part of the authoring experience. Update it
when parameters change.

### Filter

Filters transform content before or after format parsing. Purifier filters are
security-sensitive because they determine which HTML survives. Ordering can
change the result and must be tested with active plugin configuration.

### MIME

MIME plugins select templates and behavior for application, audio, image, PDF,
video, Flash, and fallback files. `lookupMimeHandler()` chooses a handler;
`getMimeTemplate()` resolves presentation.

MIME strings and filename extensions are attacker-controlled hints. Verify file
content where the processor supports it and never rely on the browser-supplied
type alone.

### Processor

Processor plugins provide image/media operations through available PHP
extensions or external tools. The checkout includes GD, ImageMagick, and
MagickWand adapters. Capability and resource limits differ by processor.

### Storage

Storage plugins connect attachment metadata to physical or external storage.
The built-in file plugin uses `liberty_files` plus filesystem storage.
`LibertyMime` centralizes storage branch, path, URL, and source-file helpers.

Never construct a storage path from a request string. Use the storage plugin
and `validateStoragePath()`.

## Adding a service

1. Choose an existing service GUID only when the semantic slot matches.
   See [Services are not a command bus](#services-are-not-a-command-bus).
2. Register from the provider package bootstrap.
3. Supply only implemented callbacks/templates.
4. Define required/optional behavior.
5. Implement both object and list authorization where relevant.
6. Test with the provider enabled and disabled.
7. Document parameter-hash and SQL additions in the provider package.

## Adding a plugin

1. Choose the correct plugin category and a stable unique GUID.
2. Place it in `plugins/` for Liberty itself or `liberty_plugins/` in a
   provider package.
3. Register function names, description, help, defaults, and capability
   metadata.
4. Treat all source and parameters as untrusted.
5. Test discovery, activation, deactivation, and cache invalidation.
6. Test alongside other active filters/plugins because order matters.

## References

- `includes/classes/LibertySystem.php`
- `includes/classes/LibertyContent.php`
- `includes/classes/LibertyMime.php`
- `includes/bit_setup_inc.php`
- `plugins/`
- [Bitweaver Liberty services documentation](https://www.bitweaver.org/wiki/LibertyServices)
- [Bitweaver Liberty formats documentation](https://www.bitweaver.org/wiki/LibertyFormats)
