describe("Url", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("url");

    cy
      .get(".k-url-field")
      .as("field");

    cy
      .get("@field")
      .find("input")
      .as("input");

  });

  it("accepts urls", () => {
    cy
      .get("@input")
      .type("https://getkirby.com")
      .should("have.value", "https://getkirby.com");

    // save bar is being shown
    cy.savebar().should("exist");

    // clearing should remove the savebar
    cy
      .get("@input")
      .clear();

    cy.savebar().should("not.exist");
  });

});
