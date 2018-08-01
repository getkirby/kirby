const reset = () => {
  cy.exec("npm run starterkit:reset");
  cy.install();
  cy.login("admin");
  cy.createUser("editor");
};

describe("UsersView", () => {

  beforeEach(() => {
    cy.login("admin");
    cy.visit("/users");
  });


  context("Load", () => {

    before(reset);

    it("visits /users", () => {
      cy.url().should('include', '/users')
      cy.get(".k-headline").should("contain", "Users");
    });

  });

  context("Role filter", () => {

    before(reset);

    beforeEach(() => {
      cy.contains(".k-dropdown", "Role: All").as("dropdown");
      cy.contains("Role: All").as("button");
      cy.get(".k-list-item").as("rows");
    });

    it("shows users for admin role", () => {
      cy.get("@button").click();
      cy.get("@dropdown").contains("Admin").click();
      cy.get("@rows").should("have.length", 1);
    });

    it("shows users for all roles", () => {
      cy.get("@button").click();
      cy.get("@dropdown").contains("All").click();
      cy.get("@rows").should("have.length", 2);
    });
  });

  context("Create new user", () => {

    before(reset);

    beforeEach(() => {
      cy.contains("Add a new user").as('button');
      cy.get('@button').should('be.visible').click();
      cy.get('.k-dialog').first().as('dialog');
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
      cy.get('@dialog').find("input[name=email]").type("peter@lustig.de");
      cy.get('@dialog').find("input[name=password]").type("password123");
      cy.get('@dialog').contains("Create").click();
      cy.contains(".k-topbar-notification", "The user has been created");
      cy.contains(".k-collection", "peter@lustig.de");
    });
  });

  context("Delete user", () => {

    before(reset);

    beforeEach(() => {
      cy.contains(".k-list-item", "editor@getkirby.com").as("row");
      cy.get("@row").find('.k-list-item-toggle').click();
      cy.get("@row").contains("Delete").click();
      cy.contains(".k-dialog", "Do you really want to delete editor@getkirby.com").as("dialog");
    });

    it("cancels", () => {
      cy.get("@dialog").contains("Cancel").click();
      cy.get("@row").contains("editor@getkirby.com");
    });

    it("deletes", () => {
      cy.get("@dialog").contains("Delete").click();
      cy.get("@row").should('not.exist');
    });
  });

  context("Rename user", () => {

    before(reset);

    beforeEach(() => {
      cy.contains(".k-list-item", "editor@getkirby.com").as("row");
      cy.get("@row").find('.k-list-item-toggle').click();
      cy.get("@row").contains("Rename").click();
      cy.contains(".k-dialog", "Name").as("dialog");
    });

    it("cancels", () => {
      cy.get("@dialog").contains("Cancel").click();
      cy.get("@row").contains("editor@getkirby.com");
    });

    it("renames", () => {
      cy.get("@dialog").find("input[name=name]").type("Editor")
      cy.get("@dialog").contains("Rename").click();
      cy.get("@row").contains('Editor');
    });

  });

  context("Change user's email", () => {

    before(reset);

    beforeEach(() => {
      cy.contains(".k-list-item", "editor@getkirby.com").as("row");
      cy.get("@row").find('.k-list-item-toggle').click();
      cy.get("@row").contains("Change email").click();
      cy.contains(".k-dialog", "Email").as("dialog");
    });

    it("cancels", () => {
      cy.get("@dialog").contains("Cancel").click();
      cy.get("@row").contains("editor@getkirby.com");
    });

    it("changes", () => {
      cy.get("@dialog").find("input[name=email]").type("peter@lustig.de")
      cy.get("@dialog").contains("Change").click();
      cy.get("@row").contains("peter@lustig.de");
    });

  });

  context("Change user's role", () => {

    before(reset);

    beforeEach(() => {
      cy.contains(".k-list-item", "editor@getkirby.com").as("row");
      cy.get("@row").find('.k-list-item-toggle').click();
      cy.get("@row").contains("Change role").click();
      cy.contains(".k-dialog", "Select a new role").as("dialog");
    });

    it("cancels", () => {
      cy.get("@dialog").contains("Cancel").click();
      cy.get("@row").contains("editor@getkirby.com").contains("Editor");
    });

    it("changes", () => {
      cy.get("@dialog").contains("Admin").click()
      cy.get("@dialog").contains("Change role").click();
      cy.get("@row").contains("editor@getkirby.com").contains("Admin");
    });

  });

  context("Change password", () => {

  });

  context("Change language", () => {

  });

  context("Permissions", () => {

  });

});
