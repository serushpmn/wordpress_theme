/**
 * Per-page-type coverage check for the split stylesheets.
 *
 * For every page context it collects the class names the matching templates
 * render, then asserts that any class which has rules in the original
 * stylesheet also has them in a module that context actually loads.
 *
 * A hit here means a page would render unstyled markup — the exact regression
 * the split risks.
 */

import fs from 'node:fs';
import path from 'node:path';
import { parseBlocks, readCss, collectSelectors } from './css-parse.mjs';

const CSS_DIR = 'assets/css';

// context -> { styles: modules loaded, templates: globs of files rendered }
const CONTEXTS = {
  'global (page/404/header/footer)': {
    styles: ['base'],
    templates: ['header.php', 'footer.php', 'page.php', '404.php', 'searchform.php', 'template-parts/content-page.php', 'template-parts/content-none.php'],
  },
  // comments.php is styled by the blog module; almasland_is_blog_context()
  // loads it on any singular screen with comments enabled.
  'singular with comments': {
    styles: ['base', 'blog'],
    templates: ['comments.php'],
  },
  'front page': {
    styles: ['base', 'front-page'],
    templates: ['front-page.php', 'template-parts/home'],
  },
  'shop / product archive': {
    styles: ['base', 'shop'],
    templates: ['woocommerce/archive-product.php', 'woocommerce/content-product.php', 'woocommerce/loop', 'inc/shop-filters.php'],
  },
  'single product': {
    styles: ['base', 'product'],
    templates: ['woocommerce/single-product.php', 'woocommerce/content-single-product.php', 'woocommerce/single-product', 'inc/used-device-health-report.php'],
  },
  cart: {
    styles: ['base', 'cart-checkout'],
    templates: ['woocommerce/cart', 'inc/cart-save-for-later.php'],
  },
  checkout: {
    styles: ['base', 'cart-checkout'],
    templates: ['woocommerce/checkout', 'woocommerce/order', 'template-parts/checkout'],
  },
  'my account': {
    styles: ['base', 'account'],
    templates: ['woocommerce/myaccount', 'woocommerce/order'],
  },
  blog: {
    styles: ['base', 'blog'],
    templates: ['home.php', 'index.php', 'archive.php', 'category.php', 'single.php', 'search.php', 'sidebar.php', 'comments.php', 'template-parts/blog', 'template-parts/content.php', 'template-parts/content-single.php', 'template-parts/content-featured.php'],
  },
};

/**
 * Reviewed and dismissed. Each entry was checked against the source stylesheet
 * and does not apply on the flagged screen even in the unsplit build.
 */
const REVIEWED = {
  'checkout.alt': 'only `.buy-card form.cart button.button.alt` — needs a .buy-card ancestor, which exists on the single product page only',
  'my account.required': 'only `.woocommerce-checkout .required` — scoped to the checkout body class',
  'my account.order-received': 'not a class; `order-received` appears in order-details.php as a WooCommerce endpoint name',
};

// Markup emitted by shared helpers, checked against the union of all modules.
const SHARED_HELPERS = [
  'inc/template-functions.php',
  'inc/product-fields.php',
  'inc/product-badges.php',
  'inc/woocommerce.php',
  'inc/nav-walker.php',
  'template-parts/content-woocommerce.php',
];

function classesInModule(file) {
  const found = new Set();
  const walk = (blocks) => {
    for (const block of blocks) {
      if (block.type === 'rule') {
        for (const cls of block.prelude.match(/\.-?[A-Za-z_][A-Za-z0-9_-]*/g) || []) found.add(cls.slice(1));
      } else if (block.nested && block.children) {
        walk(block.children);
      }
    }
  };
  walk(parseBlocks(readCss(file)));
  return found;
}

function collectPhpFiles(target) {
  const full = path.resolve(target);
  if (!fs.existsSync(full)) return [];
  if (fs.statSync(full).isDirectory()) {
    return fs
      .readdirSync(full, { recursive: true })
      .filter((f) => String(f).endsWith('.php'))
      .map((f) => path.join(full, String(f)));
  }
  return [full];
}

/** Literal class names only; `<?php ?>` interpolations are skipped. */
function classesInTemplate(file) {
  const src = fs.readFileSync(file, 'utf8');
  const found = new Set();

  for (const match of src.matchAll(/class\s*=\s*"([^"]*)"/g)) {
    for (const chunk of match[1].split(/<\?php[\s\S]*?\?>/)) {
      for (const name of chunk.split(/\s+/)) {
        if (/^[A-Za-z_][A-Za-z0-9_-]*$/.test(name)) found.add(name);
      }
    }
  }

  // Class lists built in PHP, e.g. $classes[] = 'product-card--used';
  for (const match of src.matchAll(/'(?:[a-z][a-z0-9]*-)+[a-z0-9_-]+'/g)) {
    const name = match[0].slice(1, -1);
    if (/^[a-z][A-Za-z0-9_-]*$/.test(name) && !name.includes('/') && !name.includes('.')) found.add(name);
  }

  return found;
}

const moduleClasses = new Map();
for (const file of fs.readdirSync(CSS_DIR)) {
  if (file.endsWith('.css')) moduleClasses.set(file.replace('.css', ''), classesInModule(path.join(CSS_DIR, file)));
}

const styledSomewhere = new Set();
for (const set of moduleClasses.values()) for (const c of set) styledSomewhere.add(c);

let failures = 0;

for (const [name, ctx] of Object.entries(CONTEXTS)) {
  const available = new Set();
  for (const mod of ctx.styles) {
    for (const c of moduleClasses.get(mod) || []) available.add(c);
  }

  const used = new Set();
  for (const target of ctx.templates) {
    for (const file of collectPhpFiles(target)) {
      for (const c of classesInTemplate(file)) used.add(c);
    }
  }

  // Only classes that are styled at all can regress.
  const flagged = [...used].filter((c) => styledSomewhere.has(c) && !available.has(c)).sort();
  const uncovered = flagged.filter((c) => !REVIEWED[`${name}.${c}`]);

  failures += uncovered.length;
  console.log(
    `\n${name}  [${ctx.styles.join(' + ')}]  classes used=${used.size}  UNCOVERED=${uncovered.length}  dismissed=${flagged.length - uncovered.length}`
  );
  uncovered.forEach((c) => {
    const owners = [...moduleClasses.entries()].filter(([, s]) => s.has(c)).map(([m]) => m);
    console.log(`   .${c}   styled in: ${owners.join(', ')}`);
  });
  flagged
    .filter((c) => REVIEWED[`${name}.${c}`])
    .forEach((c) => console.log(`   (dismissed) .${c} — ${REVIEWED[`${name}.${c}`]}`));
}

console.log('\n--- shared helper markup (must be styled in some module) ---');
const helperUsed = new Set();
for (const file of SHARED_HELPERS) {
  for (const c of classesInTemplate(file)) helperUsed.add(c);
}
const helperPageScoped = [...helperUsed]
  .filter((c) => styledSomewhere.has(c) && !(moduleClasses.get('base') || new Set()).has(c))
  .sort();
console.log(`classes rendered by shared helpers that are NOT in base: ${helperPageScoped.length}`);
helperPageScoped.forEach((c) => {
  const owners = [...moduleClasses.entries()].filter(([, s]) => s.has(c)).map(([m]) => m);
  console.log(`   .${c}   -> ${owners.join(', ')}`);
});

console.log(`\nTOTAL_UNCOVERED=${failures}`);
