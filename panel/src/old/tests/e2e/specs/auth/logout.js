
describe("Auth", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  context("Logout", () => {

    beforeEach(() => {
      cy.login("admin");
      cy.visit("/site");
    });

    it("redirects to login", () => {
      cy.visit("/logout");
      cy.url().should("include", "/login");
    });

    it("redirects to login after click in menu", () => {
      cy
        .get(".k-topbar-menu")
        .click()
        .contains("Logout")
        .click()
        .url()
        .should("include", "/login");
    });

  });

});
