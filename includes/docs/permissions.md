# Liberty permissions

Liberty combines package permissions, object-specific grants/revocations,
content status, ownership, and optional access-control services. No single
check is sufficient for every operation.

## Permission layers

1. **Global user permission** — supplied by Users/`BitPermUser`.
2. **Content-type permission names** — registered by the domain package and
   assigned to the object's permission members.
3. **Object-specific permission rows** — `liberty_content_permissions`.
4. **Ownership** — some edit paths grant capabilities to the content owner.
5. **Content status** — controls visibility state.
6. **Access-control services** — registered packages can impose additional
   policy.
7. **Controller context** — CSRF/challenge checks, request method, and workflow
   state still apply after authorization.

Explicit content revocations must not be accidentally overridden by a broad
grant.

## Common methods

`LibertyContent` exposes capability and enforcing variants:

| Capability | Boolean/check method | Enforcing method |
|---|---|---|
| View | `hasViewPermission()` | `verifyViewPermission()` |
| Create | `hasCreatePermission()` | `verifyCreatePermission()` |
| Edit | `hasEditPermission()` | `verifyEditPermission()` |
| Update | `hasUpdatePermission()` | `verifyUpdatePermission()` |
| Administer | `hasAdminPermission()` | `verifyAdminPermission()` |
| Expunge | `hasExpungePermission()` | `verifyExpungePermission()` |
| Post comments | `hasPostCommentsPermission()` | `verifyPostCommentsPermission()` |

Use the boolean methods for branching and filtering. Use verifier methods when
failure should terminate or feed the established error path. Confirm exact
return/error behavior at the call site before changing one for the other.

## Content-specific grants and revocations

`storePermission()` and `removePermission()` maintain group/content rows.
`isExcludedPermission()` checks explicit revocation. Role-oriented controllers
build on the same underlying model.

When changing permissions:

- Validate `content_id`, group/team identity, and permission name.
- Require administrative authority over the object.
- Use a challenge/CSRF check for browser mutations.
- Avoid exposing group membership or hidden content through error detail.
- Clear or reload any cached permission state.

## Permission-aware lists

Checking each returned object after an unrestricted query can leak counts,
titles, sort order, or timing and performs poorly. Use
`getContentListPermissionsSql()` or the established Liberty list builder so
authorization is part of the query.

Service SQL callbacks may add additional access predicates. Keep select, join,
where, and bind arrays synchronized.

## Ownership

`isOwner()` is a relationship check, not a universal edit grant. Whether an
owner can edit depends on content-type permission members, global settings,
object-specific rows, and services. Never replace `hasEditPermission()` with a
direct owner comparison.

## Status

Public/private/hidden/protected/deleted helpers describe shared status.
Authorization still applies to public content, and administrative access may
still be needed to see deleted content. Status transitions are mutations and
require update/admin authority plus request validation.

## Services

`verifyAccessControl()` and service invocation allow registered access-control
packages to restrict an object. A package adding such a service must document:

- Which lifecycle callbacks it implements.
- Whether absence means allow or deny.
- How list SQL mirrors single-object checks.
- How administrators recover from a misconfiguration.
- Cache invalidation requirements.

## Controller checklist

For every new or modified endpoint:

1. Load the object without disclosing protected fields.
2. Validate the requested action and HTTP method.
3. Check the narrowest matching Liberty capability.
4. Verify CSRF/challenge state for mutation.
5. Recheck authorization after resolving any alternate target identity.
6. Avoid rendering a protected object into error pages or logs.
