
describe("Home Page", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.login("admin");
  });

  context("Visit", () => {

    it("visits home page from site view", () => {
      cy.visit("/site");
      cy.get(".k-collection").contains("home").click();
      cy.url().should("include", "/pages/home");
      cy.get(".k-headline").should("contain", "home");
    });

  });

  context("Delete", () => {

    it("should not be able to delete the home page item", () => {
      cy
        .visit("/site")
        .get(".k-collection")
        .contains(".k-list-item", "home")
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
