// scripts/cypress-agent.js
//
// Petit « agent » local : surveille cypress/agent/request.json et, quand il apparaît,
// lance Cypress (Chrome) sur le(s) spec(s) demandé(s) via scripts/run-cypress.js,
// puis écrit le résultat dans cypress/agent/result-<id>.log (+ status-<id>.json).
//
// Usage (à lancer une fois, laisser tourner) :
//   node scripts/cypress-agent.js
//
// Format de request.json :
//   { "id": "001", "spec": "cypress/e2e/admin/xxx.cy.js", "headed": false }
//
// Sécurité : seuls des chemins sous cypress/e2e/ finissant par .cy.js sont acceptés,
// aucune autre commande n'est exécutée.

const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

const ROOT = path.resolve(__dirname, '..');
const AGENT_DIR = path.join(ROOT, 'cypress', 'agent');
const REQUEST = path.join(AGENT_DIR, 'request.json');

if (!fs.existsSync(AGENT_DIR)) fs.mkdirSync(AGENT_DIR, { recursive: true });

let busy = false;

function validSpec(spec) {
  return typeof spec === 'string'
    && /^cypress\/e2e\/[\w\-\/]+\.cy\.js$/.test(spec)
    && !spec.includes('..')
    && fs.existsSync(path.join(ROOT, spec));
}

function run(req) {
  busy = true;
  const id = String(req.id || Date.now()).replace(/[^\w-]/g, '');
  const logPath = path.join(AGENT_DIR, `result-${id}.log`);
  const statusPath = path.join(AGENT_DIR, `status-${id}.json`);
  const out = fs.createWriteStream(logPath);

  const args = ['scripts/run-cypress.js', 'run', '--browser', 'chrome', '--spec', req.spec];
  if (req.headed) args.push('--headed');

  const started = new Date().toISOString();
  fs.writeFileSync(statusPath, JSON.stringify({ id, spec: req.spec, state: 'running', started }, null, 2));
  console.log(`[agent] ▶ ${id} ${req.spec}`);

  const child = spawn('node', args, {
    cwd: ROOT,
    shell: true,
    env: { ...process.env, NO_COLOR: '1', FORCE_COLOR: '0' },
  });
  child.stdout.on('data', (d) => { out.write(d); process.stdout.write(d); });
  child.stderr.on('data', (d) => { out.write(d); process.stderr.write(d); });
  child.on('close', (code) => {
    out.end();
    fs.writeFileSync(statusPath, JSON.stringify({
      id, spec: req.spec, state: 'done', exitCode: code, started, finished: new Date().toISOString(),
    }, null, 2));
    console.log(`[agent] ■ ${id} terminé (code ${code})`);
    busy = false;
  });
}

function poll() {
  if (busy || !fs.existsSync(REQUEST)) return;
  let req;
  try {
    req = JSON.parse(fs.readFileSync(REQUEST, 'utf8'));
  } catch (e) {
    return; // fichier en cours d'écriture
  }
  // Renomme la requête pour ne pas la rejouer
  fs.renameSync(REQUEST, path.join(AGENT_DIR, `request-${String(req.id || Date.now()).replace(/[^\w-]/g, '')}.done.json`));
  if (!validSpec(req.spec)) {
    console.log(`[agent] ✖ spec refusé : ${req.spec}`);
    fs.writeFileSync(path.join(AGENT_DIR, `status-${req.id}.json`), JSON.stringify({ id: req.id, state: 'rejected', spec: req.spec }));
    return;
  }
  run(req);
}

console.log(`[agent] En attente de ${path.relative(ROOT, REQUEST)} … (Ctrl+C pour arrêter)`);
setInterval(poll, 2000);
