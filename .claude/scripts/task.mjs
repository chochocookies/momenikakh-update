#!/usr/bin/env node
// Task tracker for .claude/docs/TODO.md  (no dependencies)
// node .claude/scripts/task.mjs next [n] | show ID | list [Fx] | status | start ID | done ID | block ID | estimate | check
import fs from 'node:fs';
const FILE = new URL('../docs/TODO.md', import.meta.url);
const DAYS = { S: 0.5, M: 1.5, L: 4, XL: 8 };           // assumption: effective working days, one developer + Claude Code
const PRI = { P0: 0, P1: 1, P2: 2, P3: 3 };
const STATUS = ['todo', 'doing', 'done', 'blocked', 'backlog'];
const lines = fs.readFileSync(FILE, 'utf8').split('\n');
const rows = [];
lines.forEach((ln, i) => {
  if (!/^\|\s*F\d+-\d+\s*\|/.test(ln)) return;
  const c = ln.split('|').slice(1, -1).map(s => s.trim());
  rows.push({ line: i, cells: c, id: c[0], title: c[1], pri: c[2], size: c[3],
    deps: c[4] === '-' ? [] : c[4].split(',').map(s => s.trim()), status: c[5], done: c[6] });
});
const byId = Object.fromEntries(rows.map(r => [r.id, r]));
const phase = id => +id.slice(1).split('-')[0], num = id => +id.split('-')[1];
const cmp = (a, b) => PRI[a.pri] - PRI[b.pri] || phase(a.id) - phase(b.id) || num(a.id) - num(b.id);
const ready = r => r.status === 'todo' && r.deps.every(d => byId[d]?.status === 'done');
const fmt = r => `${r.id}  [${r.pri}/${r.size}] ${r.status.padEnd(7)} ${r.title}`;
function save(id, status) {
  const r = byId[id]; if (!r) return console.error('Unknown task', id), process.exit(1);
  if (!STATUS.includes(status)) return console.error('Bad status'), process.exit(1);
  r.cells[5] = status; lines[r.line] = `| ${r.cells.join(' | ')} |`;
  fs.writeFileSync(FILE, lines.join('\n')); console.log(`${id} -> ${status}`);
}
const [cmd = 'status', arg] = process.argv.slice(2);
if (cmd === 'next') {
  const out = rows.filter(ready).sort(cmp).slice(0, +arg || 3);
  out.length ? out.forEach(r => console.log(fmt(r) + `\n    done when: ${r.done}\n    deps: ${r.deps.join(', ') || '-'}`)) : console.log('No ready task. Check blocked/doing items.');
} else if (cmd === 'show') {
  const r = byId[arg]; if (!r) { console.error('Unknown task', arg); process.exit(1); }
  const open = r.deps.filter(d => byId[d]?.status !== 'done');
  console.log(`${fmt(r)}\nDone when: ${r.done}\nDepends on: ${r.deps.join(', ') || '-'}${open.length ? `  (NOT DONE: ${open.join(', ')})` : ''}`);
} else if (cmd === 'list') {
  rows.filter(r => !arg || r.id.startsWith(arg + '-')).forEach(r => console.log(fmt(r)));
} else if (cmd === 'status') {
  const ph = [...new Set(rows.map(r => phase(r.id)))].sort((a, b) => a - b);
  ph.forEach(p => { const rs = rows.filter(r => phase(r.id) === p), n = s => rs.filter(r => r.status === s).length;
    console.log(`F${p}: ${n('done')}/${rs.length} done, ${n('doing')} doing, ${n('blocked')} blocked, ${n('backlog')} backlog`); });
  console.log(`TOTAL: ${rows.filter(r => r.status === 'done').length}/${rows.length} done`);
} else if (cmd === 'start') save(arg, 'doing');
else if (cmd === 'done') save(arg, 'done');
else if (cmd === 'block') save(arg, 'blocked');
else if (cmd === 'estimate') {
  let all = 0; const ph = [...new Set(rows.map(r => phase(r.id)))].sort((a, b) => a - b);
  ph.forEach(p => { const rs = rows.filter(r => phase(r.id) === p && r.status !== 'backlog'); const d = rs.reduce((s, r) => s + DAYS[r.size], 0);
    all += d; console.log(`F${p}: ${rs.length} tasks, ${d} working days (~${(d / 5).toFixed(1)} weeks)`); });
  console.log(`TOTAL (excl. backlog): ${all} working days (~${(all / 5).toFixed(1)} weeks). Assumes S=0.5 M=1.5 L=4 XL=8 days.`);
} else if (cmd === 'check') {
  let bad = 0; const seen = new Set();
  rows.forEach(r => { if (seen.has(r.id)) bad++, console.log('Duplicate', r.id); seen.add(r.id);
    if (!(r.pri in PRI) || !(r.size in DAYS) || !STATUS.includes(r.status)) bad++, console.log('Bad cell in', r.id);
    r.deps.forEach(d => { if (!byId[d]) bad++, console.log(r.id, 'depends on unknown', d); }); });
  console.log(bad ? `${bad} problem(s)` : `OK: ${rows.length} tasks`); process.exit(bad ? 1 : 0);
} else console.log('Commands: next [n] | show ID | list [Fx] | status | start ID | done ID | block ID | estimate | check');
