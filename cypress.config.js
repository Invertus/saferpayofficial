const { defineConfig } = require("cypress");

module.exports = defineConfig({
  e2e: {
    experimentalSessionAndOrigin: true,
    chromeWebSecurity: false,
    setupNodeEvents(on, config) {
      // implement node event listeners here
    },
  },
});
