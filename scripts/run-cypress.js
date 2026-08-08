// scripts/run-cypress.js
//
// Bascule temporairement ton .env vers .env.cypress (base de test copiée
// depuis la prod), lance Cypress, puis restaure ton .env d'origine —
// même en cas d'interruption (Ctrl+C) ou d'erreur.
//
// Usage :
//   node scripts/run-cypress.js open   -> ouvre l'UI Cypress
//   node scripts/run-cypress.js run    -> exécute les tests en headless

const fs = require("fs");
const path = require("path");
const { spawn, execSync } = require("child_process");

const ROOT = path.resolve(__dirname, "..");
const ENV_PATH = path.join(ROOT, ".env");
const ENV_BACKUP_PATH = path.join(ROOT, ".env.backup");
const ENV_CYPRESS_PATH = path.join(ROOT, ".env.cypress");

const mode = process.argv[2] || "open"; // "open" ou "run"

function fail(message) {
  console.error(`\n❌ ${message}\n`);
  process.exit(1);
}

function clearLaravelConfigCache() {
  try {
    execSync("php artisan config:clear", { cwd: ROOT, stdio: "inherit" });
  } catch (e) {
    console.warn("⚠️  Impossible de clear le cache config (pas bloquant si aucun cache actif).");
  }
}

function activateCypressEnv() {
  if (!fs.existsSync(ENV_CYPRESS_PATH)) {
    fail(
      ".env.cypress introuvable. Copie .env.cypress.example vers .env.cypress et complète les identifiants de ta base de test."
    );
  }
  if (!fs.existsSync(ENV_PATH)) {
    fail(".env introuvable à la racine du projet.");
  }

  console.log("🔄 Sauvegarde de .env -> .env.backup");
  fs.copyFileSync(ENV_PATH, ENV_BACKUP_PATH);

  console.log("🔄 Activation de .env.cypress -> .env");
  fs.copyFileSync(ENV_CYPRESS_PATH, ENV_PATH);

  clearLaravelConfigCache();
}

function restoreOriginalEnv() {
  if (fs.existsSync(ENV_BACKUP_PATH)) {
    console.log("\n🔄 Restauration de .env.backup -> .env");
    fs.copyFileSync(ENV_BACKUP_PATH, ENV_PATH);
    fs.unlinkSync(ENV_BACKUP_PATH);
    clearLaravelConfigCache();
  }
}

// Toujours restaurer le .env, même si le process est tué (Ctrl+C) ou plante
let restored = false;
function restoreOnce() {
  if (!restored) {
    restored = true;
    restoreOriginalEnv();
  }
}
process.on("exit", restoreOnce);
process.on("SIGINT", () => {
  restoreOnce();
  process.exit(1);
});
process.on("SIGTERM", () => {
  restoreOnce();
  process.exit(1);
});

// --- Exécution ---
activateCypressEnv();

console.log(`\n🚀 Lancement de Cypress (${mode}) sur la base de test...\n`);

const cypressArgs = mode === "run" ? ["cypress", "run"] : ["cypress", "open"];
const child = spawn("npx", cypressArgs, {
  cwd: ROOT,
  stdio: "inherit",
  shell: true, // nécessaire sous Windows
});

child.on("close", (code) => {
  restoreOnce();
  process.exit(code ?? 0);
});
