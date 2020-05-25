describe("Change user email", () => {

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
      .contains("Change email")
      .click();
    cy
      .contains(".k-dialog", "Email")
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

  it("changes email", () => {
    cy
      .get("@dialog")
      .find("input[name=email]")
      .type("peter@lustig.de");
    cy
      .get("@dialog")
      .contains("Change")
      .click();
    cy
      .get("@row")
      .contains("peter@lustig.de");
  });

});
