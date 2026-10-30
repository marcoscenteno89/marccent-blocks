# Marccent Blocks

The [Marccent theme](https://github.com/marcoscenteno89/marccent-theme) in WordPress: its styles, its live theme engine (palettes, light / dark, design styles) and its components as Gutenberg blocks (section, grid, card, icon, button, animation, carousel, navbar).

The server writes every block's markup (`render.php`, the theme's Button, Icon, Section, Grid, Card and NavBar in
`inc/markup.php`); a post stores
only the blocks' settings, so a new theme release changes the markup of every page at once, with nothing for the
editor to flag as invalid. Posts saved by 0.3.1 and earlier (static markup) still open: each block's `deprecated.js`
recognizes them, and they save in the new form.

Each page loads only what it uses: the core styles, the site's design style, the Font Awesome rules for the icons on the page, and the script only where something is interactive.

## One theme for every Marccent plugin

This plugin is the one place WordPress gets the Marccent theme: it registers the theme's styles (the `marccent` style
handle) and its runtime once. The Marccent WP theme and the Marccent Local and Content plugins depend on it instead of
bundling a copy, so a site running all of them loads the theme once, at one version. `marccent_blocks_theme_version()`
says which.

## Build

The theme is a release, pinned in `package.json` (`github:marcoscenteno89/marccent-theme#v1.0.0-rc.1`); to move to a new
one, change the tag and rebuild.

```sh
npm install
npm run build      # build/: blocks, runtime, styles, design styles, icons
npm run serialize  # a block spec (MARCCENT_SPEC) into validated post content (MARCCENT_OUT)
npm test           # the checks CI runs: test:markup and test:blocks
```

- `npm run test:markup` (PHP): the server's Button, Icon, Section, Grid, Card and NavBar render each case of the
  theme's markup reference
  (`marccent-theme/markup/`) as the components do.
- `npm run test:blocks`: posts saved by 0.3.1 (`tools/fixtures/0.3.1.json`) open as valid blocks and save in the
  new form; `MARCCENT_CONTENT=posts.json` runs it on a site's posts (`{ "key": "post content" }`).

To work on the theme and the plugin together, install your checkout over the release (`npm install ../../../../marccent-theme`)
and put the tag back before committing.

The site's starting theme is the `marccent_defaults` option (JSON), e.g. `{"palette":"brand","isDark":false,"design":"swiss","palettes":[{"id":"brand","primary":"#046bd2","secondary":"#1e293b"}]}`.
