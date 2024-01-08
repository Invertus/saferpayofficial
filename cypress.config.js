/**
 * NOTICE OF LICENSE
 *
 * @author    Klarna Bank AB www.klarna.com
 * @copyright Copyright (c) permanent, Klarna Bank AB
 * @license   ISC
 *
 * @see       /LICENSE
 *
 * International Registered Trademark & Property of Klarna Bank AB
 */

const { defineConfig } = require('cypress')

module.exports = defineConfig({
  chromeWebSecurity: false,
  experimentalSourceRewriting: true,
  numTestsKeptInMemory: 5,
  defaultCommandTimeout: 7000,
  projectId: 'xb89dr',
  retries: 2,
  videoCompression: 13,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      on('after:spec', (spec, results) => {
        if (results && results.video) {
          // Do we have failures for any retry attempts?
          const failures = results.tests.some((test) =>
            test.attempts.some((attempt) => attempt.state === 'failed')
          )
          if (!failures) {
            // delete the video if the spec passed and no tests retried
            fs.unlinkSync(results.video)
          }
        }
      })
      return require('./cypress/plugins/index.js')(on, config);
    },
    setupNodeEvents(on, config) {
      require("cypress-fail-fast/plugin")(on, config);
      require('cypress-terminal-report/src/installLogsPrinter')(on);
      return config;
    },
    excludeSpecPattern: ['index.php'],
    specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
  },
})
