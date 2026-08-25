# Liberty data model

The canonical install schema is `admin/schema_inc.php`. Upgrade files may alter
deployed databases, so confirm live schema before writing a migration or
production query.

## Content identity and type

### `liberty_content_types`

Registry of content type GUIDs and their handler metadata. The GUID is the
polymorphic discriminator used to resolve a `content_id` into a package class.

Key fields:

- `content_type_guid` — stable primary identifier.
- Singular/plural display names.
- `handler_package`, `handler_class`, and `handler_file`.

### `liberty_content_status`

Lookup table for shared status semantics.

### `liberty_content`

Canonical shared row for a content object.

Key fields:

- `content_id` — shared primary identity.
- `user_id` and `modifier_user_id` — owner/author and last modifier.
- `created`, `last_modified`, and `event_time`.
- `content_type_guid` — concrete handler discriminator.
- `format_guid` — parser/format plugin.
- `content_status_id`.
- `version`, `lang_code`, `title`, and `ip`.
- `options_json` — extensible options.
- `data` — current content body.

Domain package tables normally reference `content_id` and add only their
specialized fields.

## Additional content data

### `liberty_content_data`

Typed, potentially large auxiliary values keyed by `content_id` and
`data_type`. Used when data does not belong in the main row or a domain table.

### `liberty_aliases`

Alternate titles keyed by `content_id` and `alias_title`.

### `liberty_content_prefs`

Per-content preferences keyed by `(content_id, pref_name)`.

### `liberty_content_hits`

Hit count and last-hit timestamp separated from the primary content row to
avoid rewriting the entire content record for counters.

### `liberty_content_links`

Directed links from one content object to another content identity or title.
Parsers and link-maintenance logic use this for backlink and unresolved-link
behavior.

## Revision and audit data

### `liberty_content_history`

Versioned snapshots keyed by `(content_id, version)`, including modifier,
format, summary/body, IP, timestamp, and history comment.

### `liberty_action_log`

Operational audit records for content-related actions. This is not a complete
security audit substitute; logging depends on configuration and calling paths.

## Comments

### `liberty_comments`

Maps a comment content object into a thread:

- `comment_id` — comment-specific identity.
- `content_id` — the Liberty content row for the comment itself.
- `parent_id` — parent content/comment identity.
- `root_id` — root discussed content.
- Forward/reverse thread sequences.
- Anonymous display name.

Comment text and ownership remain in `liberty_content` because
`LibertyComment` extends `LibertyMime`.

## Attachments and files

### `liberty_attachments`

Associates content with a storage plugin or foreign file identity.

Important fields include `attachment_id`, `content_id`,
`attachment_plugin_guid`, `foreign_id`, `user_id`, primary flag, position,
hits, error code, and caption.

### `liberty_files`

File metadata used by the built-in file storage plugin: owner, filename, size,
and MIME type. Physical bytes live outside the database.

### `liberty_attachment_prefs`

Attachment-scoped preferences keyed by attachment and name.

### `liberty_attachment_meta_data`

Normalized attachment metadata joining attachment, type, title, and value.

### `liberty_meta_types` and `liberty_meta_titles`

Lookup tables used to normalize metadata names.

## Structures

### `liberty_structures`

Adjacency/position data for hierarchical content:

- `structure_id`.
- `root_structure_id`.
- `content_id`.
- Level, position, alias, and parent.

`LibertyStructure` performs navigation and movement. Do not update position or
parent fields independently without preserving tree invariants.

## Permissions

### `liberty_content_permissions`

Per-content group permission rows keyed by group, permission name, and content.
`is_revoked` supports explicit denial as well as grants.

The group constraint is installed after initial table creation because Liberty
and Users have a bootstrap dependency cycle.

## Processing and cache support

### `liberty_process_queue`

Tracks asynchronous or deferred content processing, including processor,
parameters, lifecycle timestamps, status, server, and log output.

### `liberty_link_cache`

Cached remote-link data and refresh time.

### `liberty_dynamic_variables`

Named large-text values used by dynamic-variable functionality.

### `liberty_copyrights`

Legacy copyright metadata.

## Sequences

The installer registers sequences for content, comments, files, attachments,
structures, and metadata lookup identifiers. Code must use the database
abstraction/installer conventions rather than assuming auto-increment behavior
is identical across supported databases.

## Relationship summary

```text
liberty_content_types ──< liberty_content >── liberty_content_status
                              │
                              ├──< liberty_content_history
                              ├──< liberty_content_data
                              ├──< liberty_content_prefs
                              ├──< liberty_aliases
                              ├──1 liberty_content_hits
                              ├──< liberty_attachments >── liberty_files/plugin
                              ├──< liberty_structures
                              ├──< liberty_content_permissions
                              ├──< liberty_comments (as root/parent/content)
                              └──< domain package tables
```
