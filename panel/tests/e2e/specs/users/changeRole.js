describe("Change user role", () => {

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
      .contains("Change role")
      .click();
    cy
      .contains(".k-dialog", "Select a new role")
      .as("dialog");
  });

  it("cancels", () => {
    cy
      .get("@dialog")
      .contains("Cancel")
      .click();
    cy
      .get("@row")
      .contains("editor@getkirby.com")
      .contains("Editor");
  });

  it("changes role", () => {
    cy
      .get("@dialog")
      .contains("Admin")
      .click();
    cy
      .get("@dialog")
      .contains("Change role")
      .click();
    cy
      .get("@row")
      .contains("editor@getkirby.com")
      .contains("Admin");
  });

});
