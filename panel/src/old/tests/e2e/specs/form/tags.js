describe("Tags", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.visitField("tags");

    cy
      .get(".k-tags-field")
      .as("field");

    cy
      .get("@field")
      .find("input")
      .as("input");

  });

  it("accepts tags", () => {
    cy
      .get("@input")
      .type("design{enter}")

    // save bar is being shown
    cy.savebar().should("exist");

    cy
      .get("@input")
      .type("{backspace}");

    cy.focused().type("{backspace}")

    // save bar is not being shown
    cy.savebar().should("not.exist");

  });

  it("accepts multiple tags", () => {

    const tags = ["design", "photography", "architecture"];

    // add all
    tags.forEach(tag => {
      cy
        .get("@input")
        .type(tag + "{enter}");

      cy
        .get("@field")
        .contains(".k-tag", tag)
        .should("exist")
    });

    // remove all
    tags.forEach(tag => {
      cy
        .get("@field")
        .contains(".k-tag", tag)
        .focus()
        .type("{backspace}");

      cy
        .get("@field")
        .contains(".k-tag", tag)
        .should("not.exist");
    });

  });

});
