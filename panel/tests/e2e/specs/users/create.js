describe("Create user", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.login("admin");
    cy.visit("/users");
    cy
      .contains("Add a new user")
      .as("button");
    cy
      .get("@button")
      .should("be.visible")
      .click();
    cy
      .get(".k-dialog")
      .first()
      .as("dialog");
  });

  it("cancels", () => {
    cy.get('@dialog')
      .should("be.visible")
      .and("contain", "Email")
      .and("contain", "Password")
      .and("contain", "Role")
      .and("contain", "Create");
    cy.get('@dialog').should("be.visible").contains("Cancel").click();
    cy.get('.k-dialog').should('not.exist');
  });

  it("creates", () => {
    cy
      .get('@dialog')
      .find("input[name=email]")
      .type("peter@lustig.de");
    cy
      .get('@dialog')
      .find("input[name=password]")
      .type("password123");
    cy
      .get('@dialog')
      .contains("Create")
      .click();
    cy
      .contains(".k-topbar-notification", ":)");
    cy
      .contains(".k-collection", "peter@lustig.de")
      .should("exist");
  });

});
