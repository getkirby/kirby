

describe("InstallationView", () => {

  before(() => {
    cy.exec("npm run starterkit:reset");
  });

  beforeEach(() => {
    cy.visit("/");
  });

  context("Load the installation view", () => {
    it("visits /", () => {
      cy.url().should('include', '/installation')
    });
  });

  context("Fill in the form", () => {

    it("fails on invalid inputs", () => {
      cy.get("input[name=email]").type("homersimpson.de");
      cy.get("input[name=password]").type("Homer" + "{enter}");
      cy.url().should("include", "/installation");
    });

    it("redirects to /site on success", () => {

      cy.fixture("users").then(users => {
        return users['admin'];
      }).then(user => {
        cy.get("input[name=email]").type(user.email);
        cy.get("input[name=password]").type(user.password + "{enter}");
        cy.get(".k-headline").should("contain", "Kirby Kitchensink");
        cy.url().should("include", "/site");
      });

    });

  });

});
