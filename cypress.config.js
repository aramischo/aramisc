const { defineConfig } = require("cypress");
const { exec } = require("child_process");
const fs = require('fs');

module.exports = defineConfig({

  allowCypressEnv: false,

  e2e: {
    baseUrl: "http://dev.aramisc.com",
    experimentalStudio: true,
    setupNodeEvents(on, config) {
      on("task", {
        // Exécute n'importe quelle commande artisan depuis un test si besoin
        // (ponctuel uniquement — la base de test n'est PAS reset automatiquement,
        // elle reste la copie de la prod telle quelle entre les runs)
        // Usage : cy.task('artisan', 'db:seed --class=UsersSeeder')
        artisan(command) {
          return new Promise((resolve, reject) => {
            exec(`php artisan ${command}`, (error, stdout, stderr) => {
              if (error) {
                console.error(stderr);
                return reject(error);
              }
              resolve(stdout);
            });
          });
        },
        logError(message) {
          const todayDate = new Date().toLocaleDateString('en-GB').replace(/\//g, '-');
          const fs = require('fs');
          const path = require('path');
          const logDir = path.join(__dirname, 'cypress', 'logs');
          const logPath = path.join(logDir, 'cypress_errors' + todayDate + '.log');

          if (!fs.existsSync(logDir)) {
            fs.mkdirSync(logDir, { recursive: true });
          }

          const timestamp = new Date().toISOString();
          fs.appendFileSync(logPath, `[${timestamp}] ${message}\n---\n`);
          return null;
        },
        // DeleteFile
        deleteFile(filePath) {
          if (fs.existsSync(filePath)) {
            fs.unlinkSync(filePath);
          }
          return null;
        },
        // FILEEXISTS
        fileExists(filePath) {
          return fs.existsSync(filePath);
        }
      });
    },

    specPattern: "cypress/e2e/**/*.cy.js",
    supportFile: "cypress/support/e2e.js",
    viewportWidth: 1280,
    viewportHeight: 800,
    video: false,
    screenshotOnRunFailure: true,
    defaultCommandTimeout: 20000,
    screenshotsFolder: 'cypress/screenshots',
    videosFolder: 'cypress/videos',
    watchForFileChanges: false

  },
});
