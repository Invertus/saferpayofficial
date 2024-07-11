
const { defineConfig } = require('cypress')
// const { initPlugin } = require("@frsource/cypress-plugin-visual-regression-diff/plugins");

module.exports = defineConfig({
  projectId: 'as4t54',
  env: {
    pluginVisualRegressionDiffConfig: { threshold: 0.01 },
    pluginVisualRegressionMaxDiffThreshold: 0.01,
    pluginVisualRegressionUpdateImages: false, // for updating or not updating the diff image automatically
    pluginVisualRegressionImagesPath: 'cypress/screenshots',
    pluginVisualRegressionScreenshotConfig: { scale: true, capture: 'fullPage' },
    pluginVisualRegressionCreateMissingImages: true, // baseline images updating
    CYPRESS_RECORD_KEY: 'f2a6bd99-2483-4909-ab73-f3428ddb70ce',
  },
  chromeWebSecurity: false,
  experimentalMemoryManagement: true,
  experimentalSourceRewriting: true,
  numTestsKeptInMemory: 5,
  defaultCommandTimeout: 30000,
  retries: 0,
  video: true,
  videoCompression: 8,
  viewportHeight: 1080,
  viewportWidth: 1920,
  e2e: {
    // baseUrl: 'https://jusvai.eu.ngrok.io',
    // CYPRESS_RECORD_KEY: 'f2a6bd99-2483-4909-ab73-f3428ddb70ce',
    // We've imported your old cypress plugins here.
    // You may want to clean this up later by importing these.
    // setupNodeEvents(on, config) {
    // //   require('./cypress/plugins/index.js')(on, config)
    //   require("cypress-fail-fast/plugin")(on, config);
    //   require('cypress-terminal-report/src/installLogsPrinter')(on);
    //   initPlugin(on, config);
    //   return config;
    // },
     setupNodeEvents(on, config) {
    //   require("cypress-fail-fast/plugin")(on, config);
    //   return config;
     },
    experimentalMemoryManagement: true,
    excludeSpecPattern: ['**/*(.)+(spec|test).+(ts|js)'],
    specPattern: 'cypress/e2e/**/*.cy.{js,jsx,ts,tsx}',
  },
})