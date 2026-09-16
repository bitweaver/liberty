# Liberty package documentation

> Engineering documentation derived from the source in this package. The
> package's `includes/` directory must be denied to direct HTTP requests.

## Purpose

Liberty is Bitweaver's polymorphic content engine and the foundation for content-bearing packages.

## Responsibility

Owns shared content persistence, history, permissions, parsing, plugins, services, comments, attachments, MIME handling, structures, and content discovery.

## Dependencies

kernel, users, themes, languages, util.

Dependency direction matters: this package may depend on the packages above;
the dependencies do not thereby depend on this package.

## Boundary

Does not define application-specific content types; packages register handlers and extend Liberty classes.

## Documentation map

- [Architecture](architecture.md) — initialization, components, and request flow.
- [Source reference](source-reference.md) — source-derived files, classes,
  controllers, schema artifacts, plugins, and templates.
- [Development guide](development.md) — safe change workflow, extension points,
  validation, and maintenance guidance.
- [Security](security.md) — trust boundaries and direct-HTTP access requirements.
- [Content lifecycle](content-lifecycle.md) — object construction, verification,
  storage, loading, parsing, listing, history, and expunge behavior.
- [Data model](data-model.md) — canonical Liberty tables and relationships.
- [Permissions](permissions.md) — global, object, service, and status checks.
- [Services and plugins](services-and-plugins.md) — extension contracts for
  packages, parsers, filters, MIME handlers, processors, and storage.
  Services attach to content lifecycle and edit/display chrome; they are not
  a command bus. On-demand work belongs in the provider package. Processor
  plugins are local image/media ops, not remote APIs.
