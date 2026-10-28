# Marccent Blocks

The [Marccent theme](https://github.com/marcoscenteno89/marccent-theme) in WordPress: its styles, its live theme engine (palettes, light / dark, design styles) and its components as Gutenberg blocks (section, grid, card, icon, button, animation, carousel, navbar).

Each page loads only what it uses: the core styles, the site's design style, the Font Awesome rules for the icons on the page, and the script only where something is interactive.

## Build

Needs marccent-theme checked out next to the WordPress folder (`package.json` links it as `file:../../../../marccent-theme`).

```sh
npm install
npm run build      # build/: blocks, runtime, styles, design styles, icons
npm run serialize  # a block spec (MARCCENT_SPEC) into validated post content (MARCCENT_OUT)
```

The site's starting theme is the `marccent_defaults` option (JSON), e.g. `{"palette":"brand","isDark":false,"design":"swiss","palettes":[{"id":"brand","primary":"#046bd2","secondary":"#1e293b"}]}`.
