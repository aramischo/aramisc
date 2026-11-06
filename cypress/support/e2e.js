// cypress/support/e2e.js
import './commands';

let uncaughtErrors = [];

// On accumule seulement, on n'appelle PAS cy.task ici
Cypress.on('uncaught:exception', (err, runnable) => {
  const message = `[UNCAUGHT EXCEPTION] Test: ${runnable ? runnable.title : 'Unknown'}\nMessage: ${err.message}\nStack: ${err.stack}`;
  uncaughtErrors.push(message);
  return false; // empêche toujours le fail du test sur exception JS
});

afterEach(function () {
  // Flush des exceptions JS accumulées pendant ce test, dans le contexte normal de la queue Cypress
  if (uncaughtErrors.length) {
    uncaughtErrors.forEach((msg) => {
      cy.task('logError', msg, { log: false });
    });
    uncaughtErrors = [];
  }

  if (this.currentTest && this.currentTest.state === 'failed') {
    const testTitle = this.currentTest.fullTitle();
    const errorMsg = this.currentTest.err ? this.currentTest.err.message : 'Unknown assertion error';
    const stack = this.currentTest.err ? this.currentTest.err.stack : '';
    const message = `[TEST FAILED] Spec: ${this.currentTest.invocationDetails ? this.currentTest.invocationDetails.relativeFile : ''}\nTest: ${testTitle}\nError: ${errorMsg}\nStack: ${stack}`;
    cy.task('logError', message, { log: false });
  }
});