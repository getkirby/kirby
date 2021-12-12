describe("SiteView", () => {
  before(() => {
    cy.visit("/env/install/minimal");
    cy.visit("/env/user/test");
  });

  beforeEach(() => {
    cy.visit("/env/auth/test");
  });

  it("should redirect to /site", () => {
    cy.visit("/panel/");
    cy.url().should("include", "/site");
  });

  it("should be active in menu", () => {
    cy.visit("/panel/site");
    cy.get(".k-topbar-menu-button").click();
    cy.get(".k-topbar-menu a:first-child").should(
      "have.attr",
      "aria-current",
      "true"
    );
  });

  it("should have site title", () => {
    cy.visit("/panel/site");
    cy.get(".k-headline-editable").should("contain", "Test");
    cy.get(".k-topbar-breadcrumb a:first-child").should("contain", "Test");
  });

  it("should update site title", () => {
    cy.visit("/panel/site");
    cy.get(".k-headline-editable").click();
    cy.get('.k-dialog input[name="title"]').type("My Site");
    cy.get(".k-dialog form").submit();
    cy.get(".k-headline-editable").should("contain", "My Site");
    cy.get(".k-topbar-breadcrumb a:first-child").should("contain", "My Site");
  });

  it("should have working preview button", () => {
    cy.visit("/panel/site");
    cy.get(".k-header-buttons .k-button").as("button");
    cy.get("@button").should("have.attr", "target", "_blank");
    cy.get("@button").should("have.attr", "href", Cypress.config().baseUrl);
  });
});
