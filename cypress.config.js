const { defineConfig } = require('cypress')

module.exports = defineConfig({
  chromeWebSecurity: false,
  experimentalSourceRewriting: true,
  numTestsKeptInMemory: 5,
  defaultCommandTimeout: 7000,
  projectId: 'xb89dr',
  retries: 0,
  videoCompression: 13,
  video: true,
  e2e: {
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    setupNodeEvents(on, config) {
      return require('./cypress/plugins/index.js')(on, config);
    },
    setupNodeEvents(on, config) {
      require("cypress-fail-fast/plugin")(on, config);
      require('cypress-terminal-report/src/installLogsPrinter')(on);
      return config;
    },
    excludeSpecPattern: ['index.php'],
    specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
    baseUrl: 'https://saferpayofficial1784.ngrok.io',
  },
})
