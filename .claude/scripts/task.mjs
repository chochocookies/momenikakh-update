#!/usr/bin/env node
// Task tracker untuk .claude/TODO.md (tanpa dependensi)
// node .claude/scripts/task.mjs status | next [n] | show ID | list [PREFIX|Mn] | blocked | decisions | start ID | done ID | block ID | estimate | check
import fs from 'node:fs';

const TODO = new URL('../TODO.md', import.meta.url);
const DECISIONS = new URL('../docs/DECISIONS.md', import.meta.url);
const DAYS = { S: 0.5, M: 1.5, L: 4, XL: 8 };          // asumsi: hari kerja efektif, satu pengembang + Claude Code
const MARK = { todo: '[ ]', doing: '[~]', done: '[x]', blocked: '[!]' };
const STATUS_OF = Object.fromEntries(Object.entries(MARK).map(([k, v]) => [v, k]));
const DEC_RESOLVED = /^(decided|accepted|rejected|diputuskan)/i;

const lines = fs.readFileSync(TODO, 'utf8').split('\n');

// ---- tugas: | [ ] | ID (owner) | Tugas | Dep | Selesai bila | Uk | M |
const rows = [];
lines.forEach((ln, i) => {
  const m = ln.match(/^\|\s*(\[[ x~!]\])\s*\|\s*([A-Z]+-\d+)(\s*\(owner\))?\s*\|/);
  if (!m) return;
  const c = ln.split('|').slice(1, -1).map(s => s.trim());
  rows.push({
    line: i, cells: c, mark: c[0], status: STATUS_OF[c[0]], id: m[2], owner: !!m[3], title: c[2],
    deps: expand(c[3]), done: c[4], size: c[5], ms: c[6] || '',
  });
});
const byId = Object.fromEntries(rows.map(r => [r.id, r]));

// ---- keputusan: | DEC-01 | Pertanyaan | Status | Memblokir |
const decs = {};
try {
  fs.readFileSync(DECISIONS, 'utf8').split('\n').forEach(ln => {
    const m = ln.match(/^\|\s*(DEC-\d+)\s*\|/);
    if (!m) return;
    const c = ln.split('|').slice(1, -1).map(s => s.trim());
    decs[m[1]] = { question: c[1], status: c[2], resolved: DEC_RESOLVED.test(c[2]) };
  });
} catch { /* DECISIONS.md tidak ada: semua DEC dianggap belum diputuskan */ }

// ---- dependensi: "A, B", "-", "–", "TPL-08…13", "EVT-01…05, SEO-04"
function expand(cell) {
  if (!cell || /^[-–—]$/.test(cell.trim())) return [];
  const out = [];
  for (const raw of cell.split(',').map(s => s.trim()).filter(Boolean)) {
    const r = raw.match(/^([A-Z]+)-(\d+)\s*(?:…|\.\.\.)\s*(\d+)$/);
    if (r) { for (let n = +r[2]; n <= +r[3]; n++) out.push(`${r[1]}-${String(n).padStart(r[2].length, '0')}`); }
    else out.push(raw);
  }
  return out;
}
const isDec = d => d.startsWith('DEC-');
const depOk = d => isDec(d) ? !!decs[d]?.resolved : byId[d]?.status === 'done';
const openDeps = r => r.deps.filter(d => !depOk(d));
const msNum = r => +(r.ms.replace('M', '') || 99);
const idx = Object.fromEntries(rows.map((r, i) => [r.id, i]));
const cmp = (a, b) => msNum(a) - msNum(b) || idx[a.id] - idx[b.id];
const ready = r => r.status === 'todo' && openDeps(r).length === 0;
const decBlocked = r => r.status === 'todo' && openDeps(r).length > 0 && openDeps(r).every(isDec);
const days = r => DAYS[r.size] ?? 0;                    // '–' (backlog) = 0
const fmt = r => `${r.id}${r.owner ? ' (owner)' : ''}  [${r.ms}/${r.size}] ${r.status.padEnd(7)} ${r.title}`;

function save(id, status) {
  const r = byId[id];
  if (!r) { console.error('Unknown task', id); process.exit(1); }
  r.cells[0] = MARK[status];
  lines[r.line] = lines[r.line].replace(/^\|\s*\[[ x~!]\]/, `| ${MARK[status]}`);
  fs.writeFileSync(TODO, lines.join('\n'));
  console.log(`${id} -> ${status}`);
}

const [cmd = 'status', arg] = process.argv.slice(2);

if (cmd === 'next') {
  const out = rows.filter(ready).sort(cmp).slice(0, +arg || 3);
  if (!out.length) console.log('Tidak ada tugas siap. Lihat `blocked` dan `decisions`.');
  out.forEach(r => console.log(`${fmt(r)}\n    selesai bila: ${r.done}\n    dep: ${r.deps.join(', ') || '-'}`));
} else if (cmd === 'show') {
  const r = byId[arg];
  if (!r) { console.error('Unknown task', arg); process.exit(1); }
  const open = openDeps(r);
  console.log(`${fmt(r)}\nSelesai bila: ${r.done}\nDep: ${r.deps.join(', ') || '-'}${open.length ? `  (BELUM: ${open.join(', ')})` : ''}`);
  open.filter(isDec).forEach(d => console.log(`  ${d}: ${decs[d]?.question ?? '(tidak ada di docs/DECISIONS.md)'} [${decs[d]?.status ?? '?'}]`));
} else if (cmd === 'list') {
  rows.filter(r => !arg || r.id.startsWith(arg + '-') || r.ms === arg).forEach(r => console.log(fmt(r)));
} else if (cmd === 'blocked') {
  const a = rows.filter(r => r.status === 'blocked');
  const b = rows.filter(decBlocked);
  console.log('Ditandai [!]:'); a.length ? a.forEach(r => console.log('  ' + fmt(r))) : console.log('  -');
  console.log('Menunggu keputusan (semua dep lain sudah selesai):');
  b.length ? b.sort(cmp).forEach(r => console.log(`  ${fmt(r)}\n      menunggu ${openDeps(r).join(', ')}`)) : console.log('  -');
} else if (cmd === 'decisions') {
  Object.entries(decs).forEach(([id, d]) => {
    const blocks = rows.filter(r => r.deps.includes(id) && r.status !== 'done').map(r => r.id);
    console.log(`${id}  [${d.status}] ${d.question}${blocks.length ? `\n    memblokir: ${blocks.join(', ')}` : ''}`);
  });
} else if (cmd === 'status') {
  const ms = [...new Set(rows.map(r => r.ms))].sort((x, y) => +x.slice(1) - +y.slice(1));
  ms.forEach(m => {
    const rs = rows.filter(r => r.ms === m), n = s => rs.filter(r => r.status === s).length;
    console.log(`${m}: ${n('done')}/${rs.length} done, ${n('doing')} doing, ${n('blocked')} blocked, ${rs.filter(decBlocked).length} menunggu DEC`);
  });
  console.log(`TOTAL: ${rows.filter(r => r.status === 'done').length}/${rows.length} done`);
} else if (cmd === 'start') save(arg, 'doing');
else if (cmd === 'done') save(arg, 'done');
else if (cmd === 'block') save(arg, 'blocked');
else if (cmd === 'estimate') {
  let all = 0, left = 0;
  const ms = [...new Set(rows.map(r => r.ms))].sort((x, y) => +x.slice(1) - +y.slice(1));
  ms.forEach(m => {
    const rs = rows.filter(r => r.ms === m), d = rs.reduce((s, r) => s + days(r), 0), rest = rs.filter(r => r.status !== 'done').reduce((s, r) => s + days(r), 0);
    all += d; left += rest;
    console.log(`${m}: ${rs.length} tugas, ${d} hari kerja total, ${rest} hari tersisa (~${(rest / 5).toFixed(1)} minggu)`);
  });
  console.log(`TOTAL: ${all} hari kerja, tersisa ${left} (~${(left / 5).toFixed(1)} minggu). Asumsi S=0,5 M=1,5 L=4 XL=8; backlog (Uk "–") tidak dihitung.`);
} else if (cmd === 'check') {
  let bad = 0, warn = 0; const seen = new Set();
  rows.forEach(r => {
    if (seen.has(r.id)) { bad++; console.log('Duplikat', r.id); } seen.add(r.id);
    if (!r.status) { bad++; console.log('Status tidak dikenal di', r.id); }
    if (!(r.size in DAYS) && r.size !== '–') { bad++; console.log('Ukuran tidak dikenal di', r.id, `(${r.size})`); }
    if (!/^M[0-7]$/.test(r.ms)) { bad++; console.log('Milestone tidak dikenal di', r.id, `(${r.ms})`); }
    if (!r.done) { bad++; console.log('Kolom "Selesai bila" kosong di', r.id); }
    r.deps.forEach(d => {
      if (isDec(d) ? !decs[d] : !byId[d]) { bad++; console.log(r.id, 'bergantung pada ID yang tidak ada:', d); }
      else if (isDec(d) && !decs[d].resolved && r.status === 'done') { warn++; console.log(`PERINGATAN: ${r.id} sudah selesai tetapi ${d} masih ${decs[d].status}`); }
      else if (!isDec(d) && r.status === 'done' && byId[d].status !== 'done') { warn++; console.log(`PERINGATAN: ${r.id} selesai padahal dep ${d} belum selesai`); }
    });
  });
  console.log(bad ? `${bad} masalah, ${warn} peringatan` : `OK: ${rows.length} tugas, ${Object.keys(decs).length} keputusan, ${warn} peringatan`);
  process.exit(bad ? 1 : 0);
} else console.log('Perintah: status | next [n] | show ID | list [PREFIX|Mn] | blocked | decisions | start ID | done ID | block ID | estimate | check');
