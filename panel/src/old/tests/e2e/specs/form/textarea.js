describe("Textarea", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("textarea");

    cy
      .get(".k-textarea-field")
      .as("field");

    cy
      .get("@field")
      .find("textarea")
      .as("input");

    cy
      .get("@field")
      .find(".k-counter")
      .as("counter");
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
