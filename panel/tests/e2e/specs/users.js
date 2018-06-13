

describe("UsersView", () => {
  context("Load", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
    });

    it("visits /users", () => {
      cy.url().should('include', '/users')
      cy.get(".kirby-headline").should("contain", "Users");
    });

    it("visits /users/(:any)", () => {
      cy.url().should('match', /\/users$/)
      cy.contains("developer@getkirby.com").click();
      cy.get(".kirby-headline").should("contain", "developer@getkirby.com");
      cy.url().should('match', /\/users\/([a-z0-9]*)$/);
    });
  });

  context("Role filter", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
      cy.contains(".kirby-dropdown", "Role: All").as("dropdown");
      cy.contains("Role: All").as("button");
      cy.get(".kirby-list-collection-item").as("rows");
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
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
      cy.contains("Add a new user").as('button');
      cy.get('.kirby-dialog').first().as('dialog');
    });

    it("cancels", () => {
      cy.get('@button').should('be.visible').click();
      cy.get('@dialog')
        .should("visible")
        .and("contain", "Email")
        .and("contain", "Password")
        .and("contain", "Role")
        .and("contain", "Create");
      cy.get('@dialog').should("be.visible").contains("Cancel").click();
      cy.wait(100);
      cy.get('@dialog').should("not.visible");
    });

    it("creates", () => {
      cy.get('@button').click();
      cy.get('@dialog').find("input[name=email]").type("peter@lustig.de");
      cy.get('@dialog').find("input[name=password]").type("password123");
      cy.get('@dialog').contains("Create").click();
      cy.contains(".kirby-notification", "The user has been created");
      cy.contains(".kirby-collection", "peter@lustig.de");
    });
  });

  context("Delete user", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
      cy.contains(".kirby-list-collection-item", "peter@lustig.de").as("row");
      cy.get("@row").find('.kirby-list-collection-options').click();
      cy.get("@row").contains("Delete").click();
      cy.contains(".kirby-dialog", "Do you really want to delete peter@lustig.de").as("dialog");
    });

    it("cancels", () => {
      cy.get("@dialog").contains("Cancel").click();
      cy.get("@row").contains("peter@lustig.de");
    });

    it("deletes", () => {
      cy.get("@dialog").contains("Delete").click();
      cy.get("@row").should('not.exist');
    });
  });

  context("Rename user", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
    });
  });

  context("Change user's role", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
    });
  });

  context("Change password", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
    });
  });

  context("Change language", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
    });
  });

  context("Permissions", () => {
    beforeEach(() => {
      cy.login("admin");
      cy.visit("/panel/users");
    });
  });
});
