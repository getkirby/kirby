
describe("Error Page", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.login("admin");
  });

  context("Visit", () => {

    it("visits error page from site view", () => {
      cy.visit("/site");
      cy.get(".k-collection").contains("error").click();
      cy.url().should("include", "/pages/error");
      cy.get(".k-headline").should("contain", "error");
    });

  });

  context("Delete", () => {

    it("should not be able to delete the error page item", () => {
      cy
        .visit("/site")
        .get(".k-collection")
        .contains(".k-list-item", "error")
        .as("row")
        .find(".k-list-item-toggle")
        .click();

      cy.get("@row")
        .find(".k-dropdown-content")
        .contains("Delete")
        .should("be.disabled")
    });

  });

});
