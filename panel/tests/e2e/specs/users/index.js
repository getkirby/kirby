describe("Delete user", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
    cy.createUser("editor");
  });

  beforeEach(() => {
    cy.login("admin");
    cy.visit("/users");
  });

  it("visits /users", () => {
    cy.url().should('include', '/users')
    cy.get(".k-headline").should("contain", "Users");
  });

  context("Role filter", () => {

    beforeEach(() => {
      cy
        .contains(".k-dropdown", "Role: All")
        .as("dropdown");
      cy
        .contains("Role: All")
        .as("button");
      cy
        .get(".k-list-item")
        .as("rows");
    });

    it("shows users for admin role", () => {
      cy
        .get("@button")
        .click();
      cy
        .get("@dropdown")
        .contains("Admin")
        .click();
      cy
        .get("@rows")
        .should("have.length", 1);
    });

    it("shows users for all roles", () => {
      cy
        .get("@button")
        .click();
      cy
        .get("@dropdown")
        .contains("All")
        .click();
      cy
        .get("@rows")
        .should("have.length", 2);
    });

  });

});
