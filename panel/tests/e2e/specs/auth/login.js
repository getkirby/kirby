
describe("Auth", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
    cy.visit("/logout");
  });

  context("Login", () => {
    beforeEach(() => {
      cy.visit("/login");
    });

    it("displays errors on login", () => {
      cy.get("input[name=email]").type("homer@simpson.de");
      cy.get("input[name=password]").type("password123{enter}");
      cy.get(".k-login-view form")
        .should("have.attr", "data-invalid", "true");
      cy.url().should("include", "/login");
    });

    it("redirects to /site on success", () => {
      cy.fixture("users").then(users => {
        return users['admin'];
      }).then(user => {
        cy.get("input[name=email]").type(user.email);
        cy.get("input[name=password]").type(user.password + "{enter}");
        cy.get(".k-headline").should("contain", "Testkit");
        cy.url().should("include", "/site");
      });
    });
  });

});
