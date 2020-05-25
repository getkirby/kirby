
describe("Search", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.login("admin");
    cy.visit("/site");

    // click the search button
    cy
      .get(".k-button[title=Search]")
      .click();

    // get a reference to the search
    cy
      .get(".k-search")
      .as("search")
      .should("exist");

  });

  it("searches", () => {

    cy.get("@search")
      .find(".k-search-input input")
      .type("home");

    cy.get("@search")
      .find("li")
      .should("contain", "home");
  });

  it("closes search", () => {

    cy.get("@search")
      .find(".k-button[title=Close]")
      .click();

    cy.get("@search").should("not.exist");

  });

});
