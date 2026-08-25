# Liberty content lifecycle

## Core inheritance model

```text
BitBase
└── LibertyBase
    ├── LibertyContent
    │   └── LibertyMime
    │       └── LibertyComment
    └── LibertyStructure
```

Domain packages normally subclass `LibertyContent` when their objects need
shared text, ownership, history, parsing, permissions, and service integration.
They subclass `LibertyMime` when objects also own attachments or a primary
file. `LibertyBase` supplies object factories without assuming that every
object maps directly to `liberty_content`.

`LibertySystem` is a separate `BitSingleton`. It is the registry for content
types, Liberty services, parser plugins, filter plugins, storage handlers,
MIME handlers, and media processors.

## Content-type registration

A content class registers a stable content type GUID and handler metadata.
Registration ultimately reaches `LibertySystem::registerContentType()`. The
handler metadata lets `LibertyBase::getLibertyObject()` resolve a `content_id`
to its concrete package class.

A registration must identify enough information to load the handler:

- Stable `content_type_guid`.
- Human-readable singular and plural names.
- Handler package.
- Handler class.
- Handler file when convention-based loading is insufficient.
- Display, edit, preview, and thumbnail callbacks or methods where applicable.

Never reuse a GUID for a different semantic type. Existing rows in
`liberty_content` and `liberty_content_types` depend on its meaning.

## Object construction and loading

The common load path is:

1. A controller validates a package primary identifier or `content_id`.
2. It constructs the domain object or uses `LibertyBase` factory methods.
3. The domain class loads its table before or after `LibertyContent::load()`,
   depending on its established implementation.
4. `LibertyContent::load()` retrieves shared content fields, ownership,
   status, type, format, version, language, options, and body data.
5. Registered service SQL callbacks can extend the query.
6. Preferences, permissions, attachments, or parsed data are loaded lazily
   when requested.

`LibertyBase::getLibertyObject()` may return cached objects. Code that mutates
an object must account for cache invalidation and must not assume a fresh
instance merely because it called the factory.

## Verification

`LibertyContent::verify()` normalizes and validates shared input before storage.
Domain subclasses extend it for package fields. A conventional subclass:

1. Checks its create or update permission.
2. Normalizes its domain fields.
3. Calls the parent verifier.
4. Adds errors to the shared parameter hash instead of silently coercing
   invalid state.
5. Stores only after verification succeeds.

The parameter hash is an in/out contract. Callers must preserve keys the parent
and service callbacks need. Renaming or unsetting a key can break optional
services even when the local package continues to work.

## Store

`LibertyContent::store()` manages the shared record. Its responsibilities
include:

- Establishing or updating the `content_id`.
- Owner and modifier identities.
- Creation and modification timestamps.
- Content type, format, language, title, status, body, and JSON options.
- History/version recording.
- Aliases and preferences when supplied.
- Parser-format verification and save hooks.
- Registered Liberty service store callbacks.
- Cache invalidation.
- Action logging where enabled.

Domain classes remain responsible for their own tables and transaction
semantics. Follow the order used by the class being changed: some domain rows
need a new `content_id`, while other validation must happen before the shared
row is written.

Do not call `store()` merely to test whether an object is valid; it has
persistence, history, service, and cache side effects.

## Parsing and filtering

Raw content and rendered content are distinct:

1. The selected format plugin verifies or transforms input for storage.
2. Pre-parse filters can normalize or redact source data.
3. The format parser converts source data to display output.
4. Data plugins expand embedded tags.
5. Post-parse filters can transform rendered output.
6. Display services can add package-defined presentation.

`LibertyContent::parseDataHash()` is the central static parsing entry point;
object parsing delegates through the same plugin system. Avoid calling a
format-plugin function directly unless implementing plugin infrastructure.

Rendered output must not be treated as trusted merely because it passed through
a parser. Active purifier/filter configuration determines the actual HTML
security boundary.

## Listing

`LibertyContent::prepGetList()` normalizes common list parameters.
`getContentList()` and the SQL-building helpers combine:

- Base `liberty_content` columns.
- Sort and pagination.
- Content type, owner, status, time, and text filters.
- Permission-aware joins and predicates.
- Registered service list SQL callbacks.
- Package-supplied joins and select fields.

Use bind variables for caller-controlled values. Service SQL hooks can alter
select, join, where, and bind arrays, so positional bindings must remain aligned.

List permission filtering is not interchangeable with checking one loaded
object. A list query must avoid disclosing the existence or title of content
the current user cannot view.

## History

Version snapshots live in `liberty_content_history`. Important operations are:

- `storeHistory()` — preserve a revision.
- `getHistory()` and `getHistoryCount()` — retrieve revisions.
- `rollbackVersion()` — restore an earlier version through normal content state.
- `removeLastVersion()` and `expungeVersion()` — remove revision rows.

History operations require the same authorization care as live content.
Historical data may contain material later hidden or removed from the current
revision.

## Expunge

`LibertyContent::expunge()` is permanent deletion, not status hiding. It
coordinates comments, permissions, preferences, aliases, history, action data,
services, and the shared content record. Subclasses must remove their domain
rows and dependent data in the order required by constraints.

`LibertyMime::expunge()` also handles attachments. Storage files and database
metadata are separate resources; failure in one half can orphan the other.

Use the established expunge API and verify expunge permission. Direct deletion
from a domain table leaves shared Liberty state behind.

## Content status

Status helpers distinguish public, hidden, protected, private, and deleted
states. Status affects discoverability and access but does not replace explicit
permissions. Never implement a new controller by checking only
`content_status_id`.

## Caching

`LibertyContent` implements `BitCacheable`. Cache keys and filesystem cache
paths derive from content identity. Store, expunge, parser changes, and service
changes may require cache invalidation. Cache content can contain permission-
sensitive rendered output, so callers must not share it across contexts unless
the existing cache contract permits that.

## Primary source

- `includes/classes/LibertyBase.php`
- `includes/classes/LibertyContent.php`
- `includes/classes/LibertyMime.php`
- `includes/classes/LibertyComment.php`
- `includes/classes/LibertyStructure.php`
- `includes/classes/LibertySystem.php`
