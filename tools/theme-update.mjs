#!/usr/bin/env node
/*
 * npm run theme:update: move Marccent Blocks to marccent-theme's latest release and check it, in one command (no
 * Claude needed unless a check fails). It sets package.json's marccent-theme to the ref, installs it, rebuilds and runs
 * npm test (settings against settings.json, links, markup conformance, saved blocks), then says what to commit.
 * Nothing is committed for you.
 *
 *   npm run theme:update               the newest release tag (v1.0.0-rc.22…)
 *   npm run theme:update -- v1.0.0     that tag
 *   npm run theme:update -- master     the theme's master as it is now (pinned by its commit), between releases
 */
import { execFileSync, spawnSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..');
const REPO = 'https://github.com/marcoscenteno89/marccent-theme.git';
const asked = process.argv[2];

const remote = (...a) =>
  execFileSync('git', ['ls-remote', ...a, REPO], { encoding: 'utf8' }).split('\n').filter(Boolean).map((l) => l.split('\t'));

// v1.0.0-rc.22 < v1.0.0 < v1.0.1: numbers compared as numbers, a pre-release before its release.
const parse = (t) => {
  const [core, pre] = t.replace(/^v/, '').split('-');
  return { core: core.split('.').map(Number), pre: pre ? pre.split('.').map((x) => (/^\d+$/.test(x) ? Number(x) : x)) : null };
};
const compare = (a, b) => {
  const x = parse(a), y = parse(b);
  for (let i = 0; i < 3; i++) if (x.core[i] !== y.core[i]) return x.core[i] - y.core[i];
  if (!x.pre || !y.pre) return x.pre ? -1 : y.pre ? 1 : 0;
  for (let i = 0; i < Math.max(x.pre.length, y.pre.length); i++) {
    if (x.pre[i] === undefined) return -1;
    if (y.pre[i] === undefined) return 1;
    if (x.pre[i] !== y.pre[i]) return typeof x.pre[i] === 'number' && typeof y.pre[i] === 'number' ? x.pre[i] - y.pre[i] : String(x.pre[i]).localeCompare(String(y.pre[i]));
  }
  return 0;
};

let ref;
if (asked === 'master') {
  ref = remote('--heads').find(([, r]) => r === 'refs/heads/master')?.[0];
  if (!ref) throw new Error('No master branch on marccent-theme');
} else {
  const tags = remote('--tags').map(([, r]) => r.replace('refs/tags/', '')).filter((t) => /^v\d+\.\d+\.\d+/.test(t) && !t.endsWith('^{}'));
  if (asked && !tags.includes(asked)) throw new Error(`marccent-theme has no tag ${asked} on GitHub (pushed? git push origin ${asked})`);
  ref = asked || tags.sort(compare).at(-1);
}

const pkgPath = join(ROOT, 'package.json');
const pkg = readFileSync(pkgPath, 'utf8');
const was = pkg.match(/"marccent-theme": "github:marcoscenteno89\/marccent-theme#([^"]+)"/)?.[1];
if (was === ref) {
  console.log(`marccent-theme is already ${ref}; running the checks anyway.`);
} else {
  writeFileSync(pkgPath, pkg.replace(`marccent-theme#${was}`, `marccent-theme#${ref}`));
  console.log(`marccent-theme: ${was} → ${ref}`);
}

const run = (cmd, args) => {
  console.log(`\n$ ${cmd} ${args.join(' ')}`);
  return spawnSync(cmd, args, { cwd: ROOT, stdio: 'inherit', shell: process.platform === 'win32' }).status === 0;
};
if (!run('npm', ['install', '--no-audit', '--no-fund'])) process.exit(1);
const built = run('npm', ['run', 'build']);
const tested = built && run('npm', ['test']);

if (tested) {
  console.log(`\nAll checks pass on marccent-theme ${ref}. Commit package.json, package-lock.json and build/ ("marccent-theme ${ref}"),`);
  console.log('then commit the new submodule commit in the wp repo.');
} else {
  console.log(`\nA check failed on marccent-theme ${ref} (above). If it's the settings check, run node tools/settings.mjs --write`);
  console.log('and review what it changed (add an upgrade step for saved content); otherwise that failure is the work to do.');
  process.exit(1);
}
