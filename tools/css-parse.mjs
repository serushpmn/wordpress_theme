// Minimal, dependency-free CSS block splitter used by the asset build scripts.
// It only needs to understand top-level structure: comments, at-rules and rules.

import fs from 'node:fs';

export function parseBlocks(css) {
  const blocks = [];
  let i = 0;
  const n = css.length;
  let pendingComments = '';

  const lineAt = (index) => css.slice(0, index).split('\n').length;

  while (i < n) {
    // Whitespace
    if (/\s/.test(css[i])) {
      pendingComments += css[i];
      i++;
      continue;
    }

    // Comment
    if (css[i] === '/' && css[i + 1] === '*') {
      const end = css.indexOf('*/', i + 2);
      const stop = end === -1 ? n : end + 2;
      pendingComments += css.slice(i, stop);
      i = stop;
      continue;
    }

    const start = i;
    const startLine = lineAt(i);

    // At-rule without a body (@import, @charset, @namespace)
    if (css[i] === '@') {
      const preludeEnd = findPreludeEnd(css, i);
      if (css[preludeEnd] === ';') {
        blocks.push({
          type: 'statement',
          prelude: css.slice(start, preludeEnd).trim(),
          text: css.slice(start, preludeEnd + 1),
          lead: pendingComments,
          startLine,
        });
        pendingComments = '';
        i = preludeEnd + 1;
        continue;
      }
    }

    // Rule or at-rule with a block
    const braceOpen = css.indexOf('{', i);
    if (braceOpen === -1) break;

    const braceClose = matchBrace(css, braceOpen);
    const prelude = css.slice(start, braceOpen).trim();
    const body = css.slice(braceOpen + 1, braceClose);

    const isAtRule = prelude.startsWith('@');
    const nested = isAtRule && /^@(media|supports|document|layer|container|scope)\b/i.test(prelude);

    blocks.push({
      type: isAtRule ? 'atrule' : 'rule',
      nested,
      prelude,
      body,
      text: css.slice(start, braceClose + 1),
      lead: pendingComments,
      startLine,
      children: nested ? parseBlocks(body) : null,
    });

    pendingComments = '';
    i = braceClose + 1;
  }

  if (pendingComments.trim()) {
    blocks.push({ type: 'trailing', text: pendingComments, lead: '', startLine: lineAt(n) });
  }

  return blocks;
}

function findPreludeEnd(css, i) {
  let depth = 0;
  for (let k = i; k < css.length; k++) {
    const c = css[k];
    if (c === '(') depth++;
    else if (c === ')') depth--;
    else if (c === ';' && depth === 0) return k;
    else if (c === '{' && depth === 0) return k;
  }
  return css.length;
}

function matchBrace(css, open) {
  let depth = 0;
  for (let k = open; k < css.length; k++) {
    const c = css[k];
    if (c === '"' || c === "'") {
      k = skipString(css, k);
      continue;
    }
    if (c === '/' && css[k + 1] === '*') {
      const end = css.indexOf('*/', k + 2);
      k = end === -1 ? css.length : end + 1;
      continue;
    }
    if (c === '{') depth++;
    else if (c === '}') {
      depth--;
      if (depth === 0) return k;
    }
  }
  return css.length - 1;
}

function skipString(css, start) {
  const quote = css[start];
  for (let k = start + 1; k < css.length; k++) {
    if (css[k] === '\\') {
      k++;
      continue;
    }
    if (css[k] === quote) return k;
  }
  return css.length - 1;
}

export function readCss(path) {
  return fs.readFileSync(path, 'utf8');
}

// Every selector appearing in a block, including inside nested at-rules.
export function collectSelectors(block, out = []) {
  if (block.type === 'rule') {
    out.push(block.prelude);
  } else if (block.nested && block.children) {
    for (const child of block.children) collectSelectors(child, out);
  }
  return out;
}
