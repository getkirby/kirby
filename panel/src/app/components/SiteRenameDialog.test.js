import stories from "./SiteRenameDialog.stories.js";

describe("SiteRenameDialog", () => {
  beforeEach(() => {
    cy.loadStory(stories.title, "Regular");
    cy.contains("Change Site Title").as("button");
  });

  it("title resets when canceled", () => {
    cy.get("@button").click();
    cy.get("input[name=title]").type("foo");
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@button").click();
    cy.get("input[name=title]").should("have.value", "Maegazine");
  });

  it("@success on submit", () => {
    cy.get("@button").click();
    cy.get("input[name=title]").type("New York Times");
    cy.emitted("success").should("be.empty");
    cy.get(".k-dialog-submit-button").click();
    cy.wait(500);
    cy.emitted("success").should("not.be.empty");
  });
})
