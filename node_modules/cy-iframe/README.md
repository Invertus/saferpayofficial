# cy-iframe

cy.iframe() typed Cypress custom command for reaching into iframes

## Installation/Setup

Assumptions: this requires your project to already have `cypress` installed before being able to leverage this Cypress custom command so make sure to install it like so beforehand if you haven't already

`npm install --save-dev cypress`

In order to install the `cy.iframe()` custom command, you must first install the package through NPM

`npm install --save-dev cy-iframe`

Then, in your `cypress/support/commands.{js|ts}` file, you will import the package to add the `cy.iframe()` custom command.

```js
import "cy-iframe";

// More custom commands like... Cypress.Commands.add("...", () => {})
```

### JavaScript Project Intellisense with VSCode

If you are in a JavaScript project and would like to take advantage of Intellisense with VSCode, you will need to add your own `jsconfig.json` file that looks something like this.

```json
{
  "include": [
    "node_modules/cypress",
    "node_modules/cy-iframe/dist/types",
    "./cypress/**/*.js"
  ]
}
```

### TypeScript Setup

If you are in a TypeScript project and use TypeScript in your Cypress tests, you will need to edit your `cypress` folder's `tsconfig.json` to include the types, `cy-iframe/types` for the `cy.iframe()` custom command like how you had to include the types for the Cypress library.

```json
{
  "compilerOptions": {
    "target": "es5",
    "lib": ["es5", "dom"],
    "types": ["cypress", "cy-iframe/types"]
  },
  "include": ["**/*.ts"]
}
```

You can still override and add more types in your own `index.d.ts` file inside your `cypress` folder like so

```ts
// type definitions for Cypress object "cy"
/// <reference types="cypress" />
declare namespace Cypress {
  interface Cypress {
    // Sample Cypress.env("testEnv") type definition to see if we can still create our own custom types here
    env(key: "testEnv"): "dev" | "staging" | "production" | undefined;
  }

  interface Chainable<Subject = any> {
    // Sample cy.login() custom command to see if we can still create our own custom types here
    login(username: string, password: string): Chainable<{ token: string }>;
  }
}
```

### Making sure your `cypress.json` lets you work with iframes

In your `cypress.json`, you'll need to set `chromeWebSecurity` to false to allow you to deal with iframes.

```json
{
  "chromeWebSecurity": false
}
```

## Usage

After installing the package and importing the command into your Cypress project, you may now use the `cy.iframe()` command in your spec files.

The custom command takes in one argument for the proper iframe selector string i.e. `iframe#someiframe`, waits for the body contents to load up, and then returns back the iframe contents for you to access and assert on.

```ts
// cy.iframe(iframeSelector: string): Chainable<Subject>

cy.iframe("iframe#someiframe").find(".card").should("be.visible");

cy.iframe("iframe#someiframe").within(() => {
  // Inside the iframe, we can do more queries here
  cy.get("a.home-link").click();
});

cy.iframe("iframe#someiframe").within(() => {
  // Inside the iframe, you may need to use calls such as .invoke to edit inputs to account for Cypress-related gotchas/issues rather than cy.get("").type()
  cy.get("input[type='text']").invoke("val", "sometextinput");
});
```

## Examples

### JavaScript Example

You may check out this repo's `samplecypressconsumerjs` folder to see an example of a JavaScript Cypress project installing and using the `cy.iframe()` custom command.

### TypeScript Example

You may check out this repo's `samplecypressconsumerts` folder to see an example of a TypeScript Cypress project installing and using the `cy.iframe()` custom command.

## Local Development

We recommend installing and starting up Cypress through the `cypress:open` command to test your changes. There are example specs that use the `cy.iframe()` command for you to test at the top level `cypress` folder and in the separate JavaScript (`samplecypressconsumerjs`) and TypeScript (`samplecypressconsumerts`) consumers.

You may also take advantage of using `npm link` to test out changes in the local built out dist folder in the sample consumer folders before publishing to npm.

## Publishing Steps

Pre-requisite: Make sure to log in to the proper npm user through `npm login`.

First, build out the assets in the `dist/` folder

`npm run build`

Set a proper version like so

`npm version {patch|minor|major}`

Publish to the npm registry

`npm publish`
