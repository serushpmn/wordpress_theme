/**
 * Verifies assets/src/main.js was split without loss and without hazards.
 *
 * 1. Coverage — every meaningful line of the original main.js exists in some module.
 * 2. Additions — lines that only exist in the modules, so the diff is reviewable.
 * 3. Collisions — the modules are classic scripts sharing one global lexical
 *    scope, so a top-level `const`/`let`/`function` declared twice across two
 *    files that can load together is a SyntaxError at runtime.
 */

import fs from 'node:fs';

const SOURCE = 'assets/src/main.js';
const MODULES = ['core', 'shop', 'product', 'cart', 'checkout', 'home'].map((n) => `assets/js/${n}.js`);

// Which modules can be on the page together (core is always present).
const CO_LOADED = [
  ['core', 'shop'],
  ['core', 'product'],
  ['core', 'cart'],
  ['core', 'checkout'],
  ['core', 'home'],
  ['shop', 'home'],
  ['cart', 'checkout'],
  ['shop', 'product'],
];

const meaningful = (text) =>
  text
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => l && !l.startsWith('//') && !l.startsWith('*') && !l.startsWith('/*'));

function counted(lines) {
  const map = new Map();
  for (const l of lines) map.set(l, (map.get(l) || 0) + 1);
  return map;
}

const source = counted(meaningful(fs.readFileSync(SOURCE, 'utf8')));
const emitted = counted(MODULES.flatMap((f) => meaningful(fs.readFileSync(f, 'utf8'))));

const missing = [];
for (const [line, count] of source) {
  const got = emitted.get(line) || 0;
  if (got < count) missing.push(`(${count} -> ${got})  ${line}`);
}

const added = [];
for (const [line, count] of emitted) {
  const had = source.get(line) || 0;
  if (count > had) added.push(`(${had} -> ${count})  ${line}`);
}

console.log(`COVERAGE sourceLines=${source.size} missing=${missing.length}`);
missing.forEach((l) => console.log(`  MISSING  ${l}`));

console.log(`\nADDED lines not present in main.js: ${added.length}`);
added.forEach((l) => console.log(`  +  ${l}`));

// --- top-level declaration collisions -------------------------------------

const declRe = /^(?:const|let|var|function|async function|class)\s+([A-Za-z_$][\w$]*)/;

const declsByModule = new Map();
for (const file of MODULES) {
  const name = file.replace(/.*\//, '').replace('.js', '');
  const names = new Set();
  let depth = 0;
  for (const raw of fs.readFileSync(file, 'utf8').split('\n')) {
    const line = raw.replace(/\/\/.*$/, '');
    if (depth === 0) {
      const m = raw.match(declRe);
      if (m) names.add(m[1]);
    }
    depth += (line.match(/[{[(]/g) || []).length - (line.match(/[}\])]/g) || []).length;
    if (depth < 0) depth = 0;
  }
  declsByModule.set(name, names);
}

const collisions = [];
for (const [a, b] of CO_LOADED) {
  for (const name of declsByModule.get(a)) {
    if (declsByModule.get(b).has(name)) collisions.push(`${name}  (${a} + ${b})`);
  }
}

console.log(`\nGLOBAL_COLLISIONS=${collisions.length}`);
collisions.forEach((c) => console.log(`  CLASH  ${c}`));

for (const [name, names] of declsByModule) {
  console.log(`${name.padEnd(9)} top-level declarations: ${names.size}`);
}
