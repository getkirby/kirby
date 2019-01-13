describe("Email", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("email");

    cy
      .get(".k-email-field")
      .as("field");

    cy
      .get("@field")
      .find("input")
      .as("input");

  });

  it("accepts emails", () => {
    cy
      .get("@input")
      .type("test@getkirby.com")
      .should("have.value", "test@getkirby.com");

    // save bar is being shown
    cy.savebar().should("exist");

    // clearing should remove the savebar
    cy
      .get("@input")
      .clear();

    cy.savebar().should("not.exist");
  });

});
