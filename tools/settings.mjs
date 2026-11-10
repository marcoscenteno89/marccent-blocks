#!/usr/bin/env node
/*
 * The blocks' settings come from marccent-theme's markup/settings.json (PRODUCT-LINE.md 5, T-029), never a copy.
 *
 *   node tools/settings.mjs           check: every mapped block.json's attributes are what settings.json says, else fail
 *   node tools/settings.mjs --write   write them (and settings-upgrade.json's step for content saved before)
 *
 * tools/settings-map.json names each block's component and every place WordPress differs, each with its reason:
 *   wordpress: attributes only the block has (a menu's slug, a form's email, content kept in an attribute);
 *   later:     the component's settings the block doesn't offer yet;
 *   rename:    an attribute the block named differently, renamed to the setting's name (old: new; a short prop's
 *              name is found by itself);
 *   differs:   settings the block keeps in its own shape (a list typed as one line of text, a type settings.json
 *              gets wrong), so it isn't silently changed.
 * Anything else not matching fails. Each setting is an attribute of the same full-word name, its type (text: string,
 * list: array) and default; a setting with no default gets the type's empty one ('' or false), so the server never
 * reads a missing key. `version` is the component's settings version, saved with the block: content without it was
 * saved before, and settings-upgrade.json says how to read it (old names, old defaults), in the editor
 * (src/shared/upgrade.js) and on the page (inc/upgrade.php); and, where a setting's name isn't yet its component's
 * prop (padding: pad), the prop, for render.php (marccent_blocks_props).
 */
import { readFileSync, writeFileSync, existsSync, readdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import { isDeepStrictEqual } from 'node:util';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const write = process.argv.includes('--write');
const read = (p) => JSON.parse(readFileSync(p, 'utf8'));
const settingsPath = [ join(ROOT, 'node_modules/marccent-theme/markup/settings.json'), process.env.MARCCENT_SETTINGS ].filter(Boolean).at(-1);
const settings = read(settingsPath);
const map = read(join(ROOT, 'tools/settings-map.json'));
const upgradePath = join(ROOT, 'settings-upgrade.json');
const upgrade = existsSync(upgradePath) ? read(upgradePath) : {};
const TYPES = { text: 'string', number: 'number', boolean: 'boolean', list: 'array', object: 'object' };
const EMPTY = { string: '', boolean: false, array: [], object: {} };

// block.json with its attributes replaced, the rest of the file as it was: one attribute a line ({ "type": "string" }),
// a long one (sample items) spread out.
const compact = (v) => Array.isArray(v) ? (v.length ? `[ ${v.map(compact).join(', ')} ]` : '[]')
  : v && typeof v === 'object' ? (Object.keys(v).length ? `{ ${Object.entries(v).map(([k, x]) => `${JSON.stringify(k)}: ${compact(x)}`).join(', ')} }` : '{}')
  : JSON.stringify(v);
const withAttributes = (text, attributes) => {
  const lines = Object.entries(attributes).map(([k, v]) => {
    const one = compact(v);
    return `\t\t${JSON.stringify(k)}: ${one.length <= 110 ? one : JSON.stringify(v, null, '\t').replace(/\n/g, '\n\t\t')}`;
  });
  const block = `"attributes": {\n${lines.join(',\n')}\n\t}`;
  const start = text.indexOf('"attributes": {');
  if (start < 0) return text.replace(/\n\t"supports"/, `\n\t${block},\n\t"supports"`);
  let depth = 0, end = start + '"attributes": '.length;
  for (; end < text.length; end++) {
    if (text[end] === '"') { end++; while (text[end] !== '"') end += text[end] === '\\' ? 2 : 1; continue; }
    if (text[end] === '{') depth++;
    if (text[end] === '}' && --depth === 0) break;
  }
  return text.slice(0, start) + block + text.slice(end + 1);
};

let failed = 0;
const fail = (msg) => { console.log(`FAIL ${msg}`); failed++; };

for (const [block, m] of Object.entries(map.blocks)) {
  const file = join(ROOT, 'src/blocks', block, 'block.json');
  if (!existsSync(file)) { fail(`${block}: in settings-map.json but there's no src/blocks/${block}`); continue; }
  const json = read(file);
  const have = json.attributes ?? {};
  if (!m.component) {
    if (!m.why) fail(`${block}: no component and no reason (why)`);
    continue;
  }
  const c = settings[m.component];
  if (!c) { fail(`${block}: ${m.component} isn't in settings.json`); continue; }
  const want = {};
  const renames = {};
  for (const [key, s] of Object.entries(c.settings ?? {})) {
    if (m.later?.[key]) continue;
    if (s.type === 'content') { if (have[key]) want[key] = have[key]; continue; }
    const named = Object.keys(m.rename ?? {}).find((o) => m.rename[o] === key) ?? s.prop;
    const old = named && named !== key && named in have && !(key in have) ? named : null;
    if (old) renames[old] = key;
    if (m.differs?.[key]) {
      if (!have[key] && !have[old]) fail(`${block}.${key}: listed in differs but the block has no such attribute`);
      want[key] = have[key] ?? have[old];
      continue;
    }
    const type = TYPES[s.type];
    if (!type) { fail(`${block}.${key}: settings type ${s.type} has no attribute type`); continue; }
    want[key] = { type, ...('default' in s ? { default: s.default } : type in EMPTY ? { default: EMPTY[type] } : {}) };
  }
  for (const [key, why] of Object.entries(m.wordpress ?? {})) {
    if (!why) fail(`${block}.${key}: a WordPress-only attribute needs its reason`);
    if (!have[key]) fail(`${block}.${key}: listed in wordpress but the block has no such attribute`);
    else want[key] = have[key];
  }
  for (const key of Object.keys(m.later ?? {})) if (!c.settings?.[key]) fail(`${block}.${key}: listed in later but ${m.component} has no such setting`);
  want.version = { type: 'number' };
  for (const key of Object.keys(have)) {
    if (!(key in want) && !(key in renames)) fail(`${block}.${key}: not one of ${m.component}'s settings; list it under wordpress (with why) or remove it`);
  }
  for (const [key, a] of Object.entries(want)) {
    const was = have[key] ?? have[Object.keys(renames).find((o) => renames[o] === key)];
    if (!isDeepStrictEqual(was, a) && !write) fail(`${block}.${key}: block.json has ${JSON.stringify(was)}, settings.json says ${JSON.stringify(a)}`);
  }
  if (write) {
    const step = upgrade[json.name]?.[0];
    if (!step) {
      // What content saved before the settings meant: the old names, and the old defaults that changed.
      const defaults = {};
      for (const [key, a] of Object.entries(want)) {
        const oldKey = Object.keys(renames).find((o) => renames[o] === key) ?? key;
        const before = have[oldKey];
        // null: it had no default then (legacy attributes leave it out; saved content takes today's).
        if (before && !isDeepStrictEqual(before.default, a.default)) defaults[key] = 'default' in before ? before.default : null;
      }
      upgrade[json.name] = { version: c.version, 0: { rename: renames, defaults } };
    }
    upgrade[json.name].version = c.version;
    // The component's prop where a setting's full-word name differs (PRODUCT-LINE.md 5.4): render.php hands props.
    const props = Object.fromEntries(Object.entries(c.settings ?? {}).filter(([k, s]) => s.prop && s.prop !== k && k in want).map(([k, s]) => [ k, s.prop ]));
    if (Object.keys(props).length) upgrade[json.name].props = props; else delete upgrade[json.name].props;
    if (!isDeepStrictEqual(want, have)) writeFileSync(file, withAttributes(readFileSync(file, 'utf8'), want));
  } else if ((upgrade[json.name]?.version ?? null) !== c.version) {
    fail(`${block}: settings-upgrade.json has version ${upgrade[json.name]?.version} for ${json.name}, ${m.component} is ${c.version} (run --write and add the step)`);
  }
}
for (const dir of readdirSync(join(ROOT, 'src/blocks'))) if (!map.blocks[dir]) fail(`${dir}: a block not in tools/settings-map.json`);

if (write) writeFileSync(upgradePath, JSON.stringify(Object.fromEntries(Object.entries(upgrade).sort()), null, '\t') + '\n');
const mapped = Object.values(map.blocks).filter((m) => m.component).length;
const count = (k) => Object.values(map.blocks).reduce((n, m) => n + Object.keys(m[k] ?? {}).length, 0);
console.log(`${failed ? 'FAIL' : 'ok  '} settings: ${mapped} blocks read settings.json (${Object.keys(map.blocks).length - mapped} without a component); WordPress-only ${count('wordpress')}, not offered yet ${count('later')}, own shape ${count('differs')}`);
process.exit(failed ? 1 : 0);
