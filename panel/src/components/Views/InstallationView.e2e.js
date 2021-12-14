describe("InstallationView", () => {
  before(() => {
    cy.visit("/env/install/minimal");
  });

  beforeEach(() => {
    cy.visit("/panel/");
  });

  it("should fail", () => {
    cy.get('input[type="email"]').type("test@getkirby.com");
    cy.get("form").submit();
    cy.get('input[type="password"]')
      .then(($el) => $el[0].checkValidity())
      .should("be.false");
  });

  it("should install and redirect to SiteView", () => {
    cy.get('input[type="email"]').type("test@getkirby.com");
    cy.get('input[type="password"]').type("super-secure-1234");
    cy.get("form").submit();

    cy.url().should("include", "/site");
  });
});
