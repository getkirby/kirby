describe("Number", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("number");

    cy
      .get(".k-number-field")
      .as("field");

    cy
      .get("@field")
      .find("input")
      .as("input");
  });

  it("accepts numbers", () => {
    cy
      .get("@input")
      .type(1234)
      .should("have.value", "1234");

    // save bar is being shown
    cy.savebar().should("exist");
  });

});
