describe("Text", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("text");

    cy
      .get(".k-text-field")
      .as("field");

    cy
      .get("@field")
      .find("input")
      .as("input");

  });

  it("accepts text", () => {
    cy
      .get("@input")
      .type("test")
      .should("have.value", "test");

    // save bar is being shown
    cy.savebar().should("exist");

    // clearing should remove the savebar
    cy
      .get("@input")
      .clear();

    cy.savebar().should("not.exist");
  });

  it("has a working counter", () => {
    cy
      .get("@field")
      .find(".k-counter")
      .as("counter");

    // four
    cy
      .get("@input")
      .clear()
      .type("test");
    cy
      .get("@counter")
      .should("contain", 4);

    // sixteen
    cy
      .get("@input")
      .clear()
      .type("something longer");
    cy
      .get("@counter")
      .should("contain", 16);

    // zero
    cy
      .get("@input")
      .clear()
    cy
      .get("@counter")
      .should("contain", 0);
  });

});
