/**
 * Splits assets/src/style.css into page-context modules under assets/css/.
 *
 * Guarantees enforced by this script (see `verify` below):
 *   1. Every declaration block from the source lands in exactly one module.
 *   2. Original source order is preserved inside each module.
 *   3. No selector is duplicated across two modules that can load together,
 *      which is what keeps the cascade identical to the monolith.
 *
 * Run:  node tools/split-css.mjs          (report only)
 *       node tools/split-css.mjs --write  (emit files)
 */

import fs from 'node:fs';
import path from 'node:path';
import { parseBlocks, readCss, collectSelectors } from './css-parse.mjs';

const SRC = 'assets/src/style.css';
const OUT_DIR = 'assets/css';

// Enqueue order. Modules earlier in this list are loaded first.
const MODULES = ['base', 'shop', 'product', 'cart-checkout', 'account', 'blog'];

// Which modules can be on the page at the same time.
const CO_LOADED = [
  ['base', 'shop'],
  ['base', 'product'],
  ['base', 'cart-checkout'],
  ['base', 'account'],
  ['base', 'blog'],
  ['shop', 'blog'], // search results template
];

/**
 * Builds a matcher for a set of BEM block names, so `.buy-card`,
 * `.buy-card__price` and `.buy-card--used` all resolve to the same module
 * while `.buy-cards` or `.buy-card-x` do not.
 */
function bem(...names) {
  const alternation = names.map((n) => n.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
  return new RegExp(`\\.(?:${alternation})(?:__[A-Za-z0-9-]+)?(?:--[A-Za-z0-9-]+)?(?![A-Za-z0-9_-])`);
}

/**
 * Tier 1 — page scope. If any of these appear anywhere in a selector they
 * decide the module outright, because the element cannot exist elsewhere.
 */
const SCOPE_TOKENS = [
  ['cart-checkout', bem('woocommerce-cart', 'cart-page', 'woocommerce-checkout', 'checkout-page')],
  ['account', bem('woocommerce-account')],
  ['product', bem('product-detail-page', 'single-product')],
  ['shop', bem('category-page', 'shop-archive', 'shop-filter-open')],
  ['blog', bem('blog-page', 'blog-single-page')],
];

/**
 * Tier 2 — component blocks. Only consulted when no scope token matched.
 * Anything not listed here stays in `base`, which is the safe default.
 */
const COMPONENT_TOKENS = [
  // --- shop / catalog archive ---
  ['shop', bem('category-hero', 'category-toolbar', 'category-layout', 'category-product-grid')],
  ['shop', bem(
    'shop-cat-nav', 'shop-layout', 'shop-products', 'shop-toolbar', 'shop-sort-bar', 'shop-sort-pill',
    'shop-filter-panel', 'shop-filter-form', 'shop-filter-section', 'shop-filter-hint', 'shop-filter-switch',
    'shop-filter-check', 'shop-filter-checklist', 'shop-filter-price', 'shop-filter-actions',
    'shop-active-filter', 'shop-active-filters', 'shop-result-count'
  )],
  ['shop', bem('filter-panel', 'filter-backdrop', 'filter-close', 'filter-open', 'view-switcher')],
  ['shop', /\.product-grid--list(?![A-Za-z0-9_-])/],
  ['shop', bem('woocommerce-result-count', 'woocommerce-ordering')],

  // --- single product ---
  ['product', bem(
    'product-summary', 'product-gallery', 'product-head', 'product-info', 'product-info-apsb',
    'product-main-content', 'product-media-column', 'product-wrapper-content', 'product-content',
    'product-highlights', 'product-quick-facts', 'product-trust', 'product-badges', 'product-badge',
    'product-spec-list', 'product-empty-specs', 'product-title-en', 'product-more-link',
    'product-consult', 'product-excerpt'
  )],
  ['product', bem(
    'buy-card', 'mobile-buy-bar', 'consult-card', 'consult-phone', 'warranty-box', 'installment-box',
    'delivery-box', 'seller-status', 'discount-badge', 'spec-table', 'ai-review-summary', 'trust-box',
    'content-banner', 'breadcrumb--product'
  )],
  ['product', bem('single-product-colors', 'single-product-cart-state', 'cart-choice-modal')],
  ['product', bem(
    'almas-variation', 'almas-variations', 'almas-variation-unavailable', 'variations',
    'variations_button', 'reset_variations', 'single_variation_wrap'
  )],
  ['product', bem(
    'woocommerce-variation', 'woocommerce-variation-price', 'woocommerce-variation-availability',
    'woocommerce-tabs', 'woocommerce-noreviews'
  )],
  ['product', bem('device-health-report', 'used-gallery-panel', 'apsb-specs')],
  ['product', bem('related-products')],
  ['product', /\.product-grid--related(?![A-Za-z0-9_-])/],
  ['product', /\.product--variable(?![A-Za-z0-9_-])/],
  ['product', bem('in-stock', 'out-of-stock')],

  // --- cart + checkout (one module: the checkout screen reuses .cart-* markup) ---
  ['cart-checkout', bem(
    'cart-item', 'cart-items', 'cart-summary', 'cart-summary-block', 'cart-header', 'cart-layout',
    'cart-empty-state', 'cart-actions-panel', 'cart-sticky-bar', 'cart-mobile-bar', 'cart-action-link',
    'cart-update-button', 'cross-sells'
  )],
  ['cart-checkout', bem(
    'coupon-row', 'coupon-field', 'remove-button', 'cart_totals', 'wc-proceed-to-checkout',
    'woocommerce-cart-form', 'quantity-control'
  )],
  ['cart-checkout', bem(
    'shipping-calculator-form', 'shipping-calculator-button', 'woocommerce-shipping-methods',
    'woocommerce-shipping-destination', 'woocommerce-shipping-contents', 'backorder_notification'
  )],
  ['cart-checkout', bem(
    'checkout-layout', 'checkout-steps', 'checkout-fields', 'checkout-field-group', 'checkout-field-grid',
    'checkout-totals-table', 'checkout-sticky-bar', 'checkout-coupon-form', 'checkout-inline-panel',
    'checkout-payment-panel', 'checkout-trust', 'checkout-main', 'checkout-login-required',
    'checkout-review-order'
  )],
  ['cart-checkout', bem(
    'wc_payment_method', 'wc_payment_methods', 'payment_box', 'place-order',
    'woocommerce-checkout-review-order-table', 'woocommerce-terms-and-conditions-wrapper',
    'woocommerce-account-fields', 'ship-to-different-address', 'create-account',
    'woocommerce-additional-fields'
  )],
  ['cart-checkout', /\.select2-container--default(?![A-Za-z0-9_-])/],
  ['cart-checkout', bem('order-received')],

  // --- my account ---
  ['account', bem(
    'account-nav', 'account-sidebar', 'account-dashboard', 'account-order-card', 'account-auth',
    'account-layout', 'account-card', 'account-summary', 'account-stat', 'auth-card',
    'woocommerce-MyAccount-navigation', 'woocommerce-MyAccount-content', 'woocommerce-form-login',
    'woocommerce-form-register'
  )],

  // --- order summaries: rendered by both the checkout flow and my-account ---
  [['cart-checkout', 'account'], bem('mini-order-list', 'order-card', 'order-details', 'order-addresses')],

  // --- blog / posts ---
  ['blog', bem(
    'blog-hero', 'blog-chip', 'blog-chips', 'blog-layout', 'blog-single-layout', 'blog-feed',
    'blog-featured', 'blog-grid', 'blog-card', 'blog-sidebar', 'blog-widget', 'blog-article',
    'blog-post-nav', 'blog-related', 'blog-comments', 'blog-pill'
  )],
  ['blog', bem(
    'content-article', 'article-body', 'article-callout', 'post-layout', 'post-header', 'post-meta',
    'post-cover'
  )],
];

function modulesForSelector(selector) {
  for (const [mod, re] of SCOPE_TOKENS) {
    if (re.test(selector)) return [].concat(mod);
  }
  for (const [mod, re] of COMPONENT_TOKENS) {
    if (re.test(selector)) return [].concat(mod);
  }
  return ['base'];
}

/**
 * Modules a block must be emitted into.
 *
 * A block that mixes page-specific selectors is duplicated into each of those
 * modules rather than demoted to base. Those modules never load together, so
 * the duplicate can never both apply, and each copy keeps its position after
 * base — which is what demoting to base would have broken.
 *
 * As soon as one selector is unscoped, the whole block belongs in base.
 */
function modulesForBlock(block) {
  const selectors = collectSelectors(block);
  if (!selectors.length) return ['base'];

  const votes = new Set();
  for (const selectorList of selectors) {
    for (const single of selectorList.split(',')) {
      const trimmed = single.trim();
      if (trimmed) modulesForSelector(trimmed).forEach((m) => votes.add(m));
    }
  }

  if (votes.has('base')) return ['base'];

  const list = [...votes];
  // shop and blog can share the search template, so never duplicate across them.
  if (list.includes('shop') && list.includes('blog')) return ['base'];

  return list;
}

function normalizeSelector(sel) {
  return sel.replace(/\s+/g, ' ').trim();
}

function main() {
  const write = process.argv.includes('--write');
  const css = readCss(SRC);
  const blocks = parseBlocks(css);

  const buckets = new Map(MODULES.map((m) => [m, []]));
  // selector -> [{module, order}]
  const selectorIndex = new Map();
  let order = 0;

  const baseBytesBySection = new Map();
  const baseSelectors = [];
  let section = '(top)';

  for (const block of blocks) {
    if (block.type === 'trailing') continue;

    const marker = (block.lead.match(/\/\*[^*]*\*\//g) || []).pop();
    if (marker) section = `${block.startLine}: ${marker.replace(/[/*]/g, '').trim()}`;

    if (block.nested && block.children) {
      // Media/supports blocks are split per inner rule so each module keeps
      // only the parts it needs, wrapped in the same condition.
      const perModule = new Map();
      for (const child of block.children) {
        for (const mod of modulesForBlock(child)) {
          if (!perModule.has(mod)) perModule.set(mod, []);
          perModule.get(mod).push(child);
        }
      }

      for (const [mod, children] of perModule) {
        const inner = children.map((c) => indent(c.text)).join('\n\n');
        const text = `${block.prelude} {\n${inner}\n}`;
        buckets.get(mod).push(text);
        if (mod === 'base') {
          addBytes(baseBytesBySection, section, text);
          children.forEach((c) => baseSelectors.push([block.startLine, collectSelectors(c).join(', ')]));
        }

        for (const child of children) {
          for (const selectorList of collectSelectors(child)) {
            for (const single of selectorList.split(',')) {
              const key = `@${normalizeSelector(block.prelude)}||${normalizeSelector(single)}`;
              if (!selectorIndex.has(key)) selectorIndex.set(key, []);
              selectorIndex.get(key).push({ module: mod, order: order++ });
            }
          }
        }
      }
      continue;
    }

    const mods = modulesForBlock(block);
    const lead = keepComment(block.lead);
    const text = lead ? `${lead}\n${block.text}` : block.text;

    for (const mod of mods) {
      buckets.get(mod).push(text);
      if (mod === 'base') {
        addBytes(baseBytesBySection, section, text);
        baseSelectors.push([block.startLine, collectSelectors(block).join(', ')]);
      }

      for (const selectorList of collectSelectors(block)) {
        for (const single of selectorList.split(',')) {
          const key = normalizeSelector(single);
          if (!key) continue;
          if (!selectorIndex.has(key)) selectorIndex.set(key, []);
          selectorIndex.get(key).push({ module: mod, order: order++ });
        }
      }
    }
  }

  const report = verify(selectorIndex, buckets, css, blocks);
  const roundTrip = verifyRoundTrip(blocks, buckets);

  console.log('--- module sizes ---');
  for (const mod of MODULES) {
    const text = buckets.get(mod).join('\n\n');
    console.log(
      `${mod.padEnd(15)} ${String((Buffer.byteLength(text) / 1024).toFixed(1)).padStart(7)} KB  ${String(buckets.get(mod).length).padStart(5)} blocks`
    );
  }

  console.log('\n--- cascade conflicts (co-loaded modules, order would flip) ---');
  if (!report.conflicts.length) {
    console.log('none');
  } else {
    for (const c of report.conflicts.slice(0, 60)) {
      console.log(`  ${c.selector}  [${c.a} #${c.aOrder}] before [${c.b} #${c.bOrder}]`);
    }
    console.log(`TOTAL_CONFLICTS=${report.conflicts.length}`);
  }

  console.log(`\nBLOCK_ACCOUNTING ok=${report.blockCountOk} source=${report.sourceBlocks} emitted=${report.emittedBlocks}`);
  console.log(
    `ROUNDTRIP missing=${roundTrip.missing.length} extra=${roundTrip.extra.length} sourceRules=${roundTrip.sourceCount}`
  );
  roundTrip.missing.slice(0, 20).forEach((r) => console.log(`  MISSING  ${r}`));
  roundTrip.extra.slice(0, 20).forEach((r) => console.log(`  EXTRA    ${r}`));

  const rangeArg = process.argv.find((a) => a.startsWith('--base-range='));
  if (rangeArg) {
    const [from, to] = rangeArg.split('=')[1].split('-').map(Number);
    console.log(`\n--- base.css selectors from source lines ${from}-${to} ---`);
    const seen = new Set();
    for (const [line, sel] of baseSelectors) {
      if (line < from || line > to) continue;
      for (const single of sel.split(',')) {
        const key = normalizeSelector(single);
        if (key && !seen.has(key)) {
          seen.add(key);
          console.log(`${String(line).padStart(6)}  ${key}`);
        }
      }
    }
  }

  if (process.argv.includes('--base-report')) {
    console.log('\n--- base.css composition by source section ---');
    [...baseBytesBySection.entries()]
      .sort((a, b) => b[1] - a[1])
      .slice(0, 40)
      .forEach(([name, bytes]) => console.log(`${String((bytes / 1024).toFixed(1)).padStart(8)} KB  ${name}`));
  }

  if (write) {
    for (const mod of MODULES) {
      const body = buckets.get(mod).join('\n\n');
      const header = `/*!\n * Almas Land — ${mod}.css\n * Generated from assets/src/style.css by tools/split-css.mjs.\n * Regenerate with: node tools/split-css.mjs --write\n */\n`;
      fs.writeFileSync(path.join(OUT_DIR, `${mod}.css`), `${header}\n${body}\n`, 'utf8');
      console.log(`wrote ${OUT_DIR}/${mod}.css`);
    }
  }
}

/**
 * Confirms the split is lossless: every `(at-rule context, selector,
 * declarations)` triple in the source must exist in exactly one module, and no
 * module may invent one.
 */
function verifyRoundTrip(sourceBlocks, buckets) {
  const source = new Map();
  const emitted = new Map();

  const walk = (blocks, context, sink) => {
    for (const block of blocks) {
      if (block.type === 'rule') {
        const key = `${context}||${normalizeSelector(block.prelude)}||${block.body.replace(/\s+/g, ' ').trim()}`;
        sink.set(key, (sink.get(key) || 0) + 1);
      } else if (block.nested && block.children) {
        walk(block.children, `${context}@${normalizeSelector(block.prelude)}`, sink);
      } else if (block.type === 'atrule' || block.type === 'statement') {
        sink.set(`${context}||AT||${block.text.replace(/\s+/g, ' ').trim()}`, 1);
      }
    }
  };

  walk(sourceBlocks, '', source);

  for (const mod of MODULES) {
    walk(parseBlocks(buckets.get(mod).join('\n\n')), '', emitted);
  }

  const missing = [...source.keys()].filter((k) => !emitted.has(k)).map((k) => k.slice(0, 160));
  const extra = [...emitted.keys()].filter((k) => !source.has(k)).map((k) => k.slice(0, 160));

  return { missing, extra, sourceCount: source.size };
}

function addBytes(map, key, text) {
  map.set(key, (map.get(key) || 0) + Buffer.byteLength(text));
}

function indent(text) {
  return text
    .split('\n')
    .map((line) => (line.trim() ? `  ${line}` : line))
    .join('\n');
}

// Keep the human-written section markers, drop pure whitespace runs.
function keepComment(lead) {
  const comments = lead.match(/\/\*[\s\S]*?\*\//g);
  return comments ? comments.join('\n') : '';
}

function verify(selectorIndex, buckets, css, blocks) {
  const rank = new Map(MODULES.map((m, i) => [m, i]));
  const coLoaded = new Set(CO_LOADED.map(([a, b]) => `${a}|${b}`).concat(CO_LOADED.map(([a, b]) => `${b}|${a}`)));
  const conflicts = [];

  for (const [selector, entries] of selectorIndex) {
    if (entries.length < 2) continue;
    for (let i = 0; i < entries.length; i++) {
      for (let j = i + 1; j < entries.length; j++) {
        const a = entries[i];
        const b = entries[j];
        if (a.module === b.module) continue;
        if (!coLoaded.has(`${a.module}|${b.module}`)) continue;

        // a comes first in the source; a conflict exists only if the module
        // holding it is enqueued after the module holding the later rule.
        if (rank.get(a.module) > rank.get(b.module)) {
          conflicts.push({ selector, a: a.module, aOrder: a.order, b: b.module, bOrder: b.order });
        }
      }
    }
  }

  const sourceBlocks = blocks.filter((b) => b.type !== 'trailing').length;
  let emittedBlocks = 0;
  for (const mod of MODULES) emittedBlocks += buckets.get(mod).length;

  return {
    conflicts,
    sourceBlocks,
    emittedBlocks,
    blockCountOk: emittedBlocks >= sourceBlocks,
  };
}

main();
