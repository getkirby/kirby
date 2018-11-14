describe("Tel", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("tel");

    cy
      .get(".k-tel-field")
      .as("field");

    cy
      .get("@field")
      .find("input")
      .as("input");

  });

  it("accepts phone numbers", () => {
    cy
      .get("@input")
      .type("+49 1234 5678")
      .should("have.value", "+49 1234 5678");

    // save bar is being shown
    cy.savebar().should("exist");

    // clearing should remove the savebar
    cy
      .get("@input")
      .clear();

    cy.savebar().should("not.exist");

  });

});
