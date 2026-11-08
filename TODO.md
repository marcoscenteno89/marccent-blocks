# To do: marccent-blocks

The rules for this list are in [PRODUCT-LINE.md §8](https://github.com/marcoscenteno89/marccent-theme/blob/master/PRODUCT-LINE.md#8-work-lists-ticketsmd-and-each-projects-todomd):
work fixed here only; anything the theme fixes is a ticket in marccent-theme's
[TICKETS.md](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md), linked under Waiting on.

## Bugs

- [ ] **Forms behind a proxy**: the rate limit is per `REMOTE_ADDR`; behind Caddy every visitor has Caddy's, so one
      person can use up a form's tries for everyone. Set the real visitor IP from a trusted proxy's `X-Forwarded-For`
      (in `wp-config.php`, only for the proxy's address) before forms go live. (found 2026-10-31)

- [x] **A form in a template part or synced pattern** isn't found when it's submitted (only the page's own content was
      read). Fixed 2026-11-05: synced patterns and template parts are followed, and the theme's parts searched. (found 2026-11-05)

- [x] **Map block's pins missing from the server markup** — `marccent_blocks_map` writes `data-markers` now (0.6.0); the test passes. Left: the Map block has no pins setting yet (found 2026-11-05)

## Requests

- [ ] **Commit and release the work not committed yet** — listed for 0.5.0 (found 2026-10-31):
  - Container blocks registered with `skip_inner_blocks`: they render their blocks themselves (`marccent_blocks_inner`),
    and WordPress rendered them first too, unused, doubling with each nested container (crm-sample's Accounts page:
    45,403 block renders, now 1,153, the same HTML).
  - A `{{ token }}` inside a link's URL is URL-encoded before `esc_url`, so it isn't stripped.
  - New blocks wrapping the theme's components:
    - Server-written, from the markup reference (tested in `tools/markup-test.php`, 30 cases): Accordion (+ item),
      Alert, Modal, Drawer, Tooltip, Popover.
    - Islands (`inc/islands.php`, `src/shared/island.js`, `src/runtime/islands/`, found by file name): Timeline, Tabs (+
      tab), Step bar, Stat card, Image card, Ticket card, Hex wall, Collage, Compare, Data table, Code, Stars, Dock,
      Radial menu, Mini theme editor, Theme editor, Countdown, Orbit, Parallax, Scenery, Clock, Stopwatch.
    - Forms: Form, Field, Rating, Tags (`inc/forms.php`): the form's fields read from the published page (never the
      request), a nonce, type checks, length limits, 5 tries per 10 minutes per visitor; entries kept (Forms → Entries,
      administrators only) then emailed; Cloudflare Turnstile once its keys are set (Settings → Marccent forms).

- [ ] **Markup test: use the theme's conformance check** — `tools/markup-render.php` prints the cases for marccent-theme's `npm run conformance`; once the pinned release ships `scripts/conformance.mjs`, `test:markup` runs it (`--reference node_modules/@marccent/theme/markup/reference.json --render php tools/markup-render.php`) and the comparison in `tools/markup-test.php` goes (keep its href tests). Not covered yet: menu, toggleButton. (found 2026-11-07)

- [ ] **Islands have no markup reference**: their stand-in is plain text from their props, not the component's markup.
      Adding them to marccent-theme's `markup/cases.json` would let the server write the real markup (no flash, no
      JavaScript needed for the static ones: Timeline, Stat card, Ticket card, Image card, Hex wall, Stars). (found 2026-10-31)

- [ ] **Navbar shows any menu made in Appearance → Menus** — a menu picker instead of the fixed Primary location, so the block is dynamic and can go anywhere (header, footer, a sidebar); the menus stay edited in wp-admin. (found 2026-11-06)

- [ ] **Remove the Social links block** — not needed; the Social menu location and the Navbar's social links go with it (rc-imp's header uses `marccent/social`: move it to a menu). (found 2026-11-06)

- [ ] **One Card block** — Image card (and an icon card) become patterns of the Card block, not blocks of their own (crm-sample's showcase uses `marccent/image-card`). (found 2026-11-06)

- [ ] **Item lists come from field rows, not the block** — Orbit's items (and Dock, Step bar, Hex wall, Ticket card's list, Radial menu, Collage, Timeline: every block with an `items` array) are typed into the block's settings; they repeat, so they're a field group's rows (or posts) edited in wp-admin, and the block reads them from a Loop source. (found 2026-11-06)
  - Also typed in: Data table's rows (a CSV in the block), Hex wall's people, Collage's tiles, Map's single marker (one `lat`/`lng` typed in; the places are rows, and the names should be `latitude`/`longitude`).
  - Accordion and Tabs take only their own items (`allowedBlocks`), so a Loop can't go inside them: a FAQ from rows can't be an Accordion. Allow a Loop of items inside, or let them read a Loop source.
  - Carousel's slides are blocks placed by hand; a Loop of slides should work inside it.
  - Fine as they are: Form field's options (the form's own setup), Scenery's layers and Section's dividers (looks, not content).

- [ ] **A Carousel of a Loop's items**: a Loop inside a Carousel is one slide (with Marccent Content's Loop). (found 2026-10-31)

- [ ] **Collage** draws its tiles as `ImageCard`s inside the theme's `Collage`, with copies of its `SIZE` and `BORDER`
      tables (`src/shared/collage.js`): the theme's `Collage` only takes its own photos (`/img/<name>.webp`). Better: the
      theme's `Collage` taking a tile's `src`, then drop the copies. (found 2026-10-31)

- [ ] **Orbit** offers items on a ring, or the theme's `OrbitClock` / `SolarSystem`; `wheel`, `center`, `offset` and
      `sync` aren't in its settings. (found 2026-10-31)

- [ ] **File drop** in forms: uploads from the public need type and size checks, storage outside the media library and
      cleanup; a separate security review. (found 2026-10-31)

- [ ] **Turnstile's server check** is untested (no keys on crm-sample): test with Cloudflare's test keys
      (always-pass and always-fail) before relying on it. (found 2026-10-31)

- [ ] **Retire Forms → Entries (`marccent_entry`)**: it's a post type made in code (RULES.md A9), kept only as the
      fallback for a form with no "Keep entries in" or a site without Marccent Content. Once every site's forms keep
      theirs in a Fields type: move any old entries into it, then remove the type, its screen and its CSV export.
      (found 2026-11-05)

- [x] **Save entries to a Fields type** (RULES.md A9, A10): the Form block's Entries → "Keep entries in" names a post
      type made in Fields; Marccent Content saves each entry there as a private post (its JSON field gets everything
      sent; fields named as form fields, `form`, `page` and `status` get theirs), then the email and webhook run as
      before. rc-imp's two forms keep theirs in its Entries type. Done 2026-11-05. (found 2026-11-05)

- [x] **One Settings → Marccent page for keys and site settings** — keys done (0.5.6: Appearance → Marccent settings; Brevo and Sentry added 2026-11-04, handed to WP Mail SMTP / WP Sentry Integration). Tracking codes stay in Marccent Local; no top-level menu. Was: — today they're spread out: Maps key (Settings →
      Business), Turnstile (Settings → Marccent forms), tracking codes (Marccent Local), Brevo/Sentry only in
      wp-config/.env. Each plugin registers its keys (a filter, as `marccent_export_parts`); a key set in .env/wp-config
      shows locked, secrets masked and left out of exports, plus a "what's missing" check. One top-level **Marccent**
      menu with tabs, the theme's Appearance → Marccent theme page moving into it. (found 2026-11-04)

## Waiting on

- [ ] **[T-004](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — an Opening hours block (open now from the site's time zone, hours from field rows) (found 2026-11-08)
- [ ] **[T-005](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — a Breadcrumbs block built from where the page sits (found 2026-11-08)
- [ ] **[T-007](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — maybe a Fact block (one label / value pair, a Loop wraps them in the list) (found 2026-11-08)
- [ ] **[T-008](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — the theme's `.table` on core table blocks, so Marccent Local's table CSS goes (found 2026-11-08)
- [ ] **[T-022](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — the editor panels are hand-built (found 2026-11-08)
- [ ] **[T-024](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — `typeset` in PHP on rendered blocks' text (found 2026-11-08)
- [ ] **[T-025](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — a Price block (found 2026-11-08)
- [ ] **[T-026](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — a Date block (found 2026-11-08)
- [ ] **[T-027](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — a Contact block (found 2026-11-08)
- [ ] **[T-028](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — text formats in the block editor (Raised, Muted, Badge) (found 2026-11-08)
- [ ] **[T-029](https://github.com/marcoscenteno89/marccent-theme/blob/master/TICKETS.md)** — read the theme's settings file instead of `block.json`'s and `src/shared/options.js`'s own copies (found 2026-11-08)

## After 1.0

- [ ] **Entries in a Fields type have no CSV export or per-form filter** (Forms → Entries had both): a general tool
      for any Fields type (a list's columns and an export in Marccent Content), not one for entries. (found 2026-11-05)

## Notes

- Speed pass, not committed: `marccent_blocks_option()` keeps `marccent_defaults` and `marccent_turnstile` autoloaded
  (a missing option was a query on every page); the forms' admin screens in `inc/forms-admin.php`, loaded only in the
  admin; `marccent_forms_field_base()` for the fields' shared preamble. Peer review before the commit.
- **Cache the navbar's menu tree** (`marccent_blocks_menu_tree`, every page): an estimated 4–5 queries with a menu
  assigned. Keep it in an autoloaded option, cleared on menu, post, term, theme-mod, permalink and home changes;
  it would skip `wp_get_nav_menu_items` filters that change a menu per request.
- **The navbar's custom logo** costs 2 queries a page (`wp_get_attachment_image_src`); keep it with the cached menu
  tree.
