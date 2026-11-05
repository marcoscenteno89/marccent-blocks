# To do

- [ ] Commit and release (0.5.0). Not committed yet:
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
- [ ] **Collage** draws its tiles as `ImageCard`s inside the theme's `Collage`, with copies of its `SIZE` and `BORDER`
      tables (`src/shared/collage.js`): the theme's `Collage` only takes its own photos (`/img/<name>.webp`). Better: the
      theme's `Collage` taking a tile's `src`, then drop the copies.
- [ ] **Orbit** offers items on a ring, or the theme's `OrbitClock` / `SolarSystem`; `wheel`, `center`, `offset` and
      `sync` aren't in its settings.
- [ ] **Islands have no markup reference**: their stand-in is plain text from their props, not the component's markup.
      Adding them to marccent-theme's `markup/cases.json` would let the server write the real markup (no flash, no
      JavaScript needed for the static ones: Timeline, Stat card, Ticket card, Image card, Hex wall, Stars).
- [ ] **File drop** in forms: uploads from the public need type and size checks, storage outside the media library and
      cleanup; a separate security review.
- [ ] **A Carousel of a Loop's items**: a Loop inside a Carousel is one slide (with Marccent Content's Loop).
- [ ] **Forms behind a proxy**: the rate limit is per `REMOTE_ADDR`; behind Caddy every visitor has Caddy's, so one
      person can use up a form's tries for everyone. Set the real visitor IP from a trusted proxy's `X-Forwarded-For`
      (in `wp-config.php`, only for the proxy's address) before forms go live.
- [ ] **Turnstile's server check** is untested (no keys on crm-sample): test with Cloudflare's test keys
      (always-pass and always-fail) before relying on it.
- [ ] **A form in a template part or synced pattern** isn't found when it's submitted (only the page's own content is
      read).

## Notes

- Speed pass, not committed: `marccent_blocks_option()` keeps `marccent_defaults` and `marccent_turnstile` autoloaded
  (a missing option was a query on every page); the forms' admin screens in `inc/forms-admin.php`, loaded only in the
  admin; `marccent_forms_field_base()` for the fields' shared preamble. Peer review before the commit.
- **Cache the navbar's menu tree** (`marccent_blocks_menu_tree`, every page): an estimated 4–5 queries with a menu
  assigned. Keep it in an autoloaded option, cleared on menu, post, term, theme-mod, permalink and home changes;
  it would skip `wp_get_nav_menu_items` filters that change a menu per request.
- **The navbar's custom logo** costs 2 queries a page (`wp_get_attachment_image_src`); keep it with the cached menu
  tree.

- [x] **One Settings → Marccent page for keys and site settings** — keys done (0.5.6: Appearance → Marccent settings; Brevo and Sentry added 2026-11-04, handed to WP Mail SMTP / WP Sentry Integration). Tracking codes stay in Marccent Local; no top-level menu. Was: — today they're spread out: Maps key (Settings →
      Business), Turnstile (Settings → Marccent forms), tracking codes (Marccent Local), Brevo/Sentry only in
      wp-config/.env. Each plugin registers its keys (a filter, as `marccent_export_parts`); a key set in .env/wp-config
      shows locked, secrets masked and left out of exports, plus a "what's missing" check. One top-level **Marccent**
      menu with tabs, the theme's Appearance → Marccent theme page moving into it. (found 2026-11-04)
