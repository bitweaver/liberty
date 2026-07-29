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
stored, and parsed. Current files include BBCode, BitHTML, Markdown, PEAR Wiki,
simple text, and TikiWiki handlers.

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
