describe("Delete user", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
    cy.createUser("editor");
  });

  beforeEach(() => {
    cy.login("admin");
    cy.visit("/users");
    cy
      .contains(".k-list-item", "editor@getkirby.com")
      .as("row");
    cy
      .get("@row")
      .find('.k-list-item-toggle')
      .click();
    cy
      .get("@row")
      .contains("Delete")
      .click();
    cy
      .contains(".k-dialog", "Do you really want to delete editor@getkirby.com")
      .as("dialog");
  });

  it("cancels", () => {
    cy
      .get("@dialog")
      .contains("Cancel")
      .click();
    cy
      .get("@row")
      .contains("editor@getkirby.com");
  });

  it("deletes", () => {
    cy
      .get("@dialog")
      .contains("Delete")
      .click();
    cy
      .get("@row")
      .should('not.exist');
  });

});
