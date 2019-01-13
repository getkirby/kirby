describe("Change user name", () => {

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
      .contains("Rename")
      .click();
    cy
      .contains(".k-dialog", "Name")
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

  it("renames", () => {
    cy
      .get("@dialog")
      .find("input[name=name]")
      .type("Editor")
    cy
      .get("@dialog")
      .contains("Rename")
      .click();
    cy
      .get("@row")
      .contains('Editor');
  });

});
