import stories from "./PageDialogs.stories.js";

const prepare = () => {
  cy.loadStory(stories.title, "Regular");
  cy.get("[data-cy=pages]").children().as("pages");
  cy.get("@pages").first().as("page");
  cy.get("@page").find(".k-options-dropdown").as("options");
  cy.get(".k-code-block").as("code");

}

describe("PageDialogs", () => {
  beforeEach(prepare);

  it("check story", () => {
    cy.get("@pages").should("exist");
    cy.get("@pages").should("have.length", 1);
    cy.get("@page").should("exist");
  });
})

describe("PageRenameDialog", () => {
  beforeEach(prepare);

  it("cancel", () => {
    cy.get("@code").should("contains.text", '"title": "Animals"');
    cy.get("@page").should("contains.text", "Animals");
    cy.get("@options").click();
    cy.get("@options").contains("Rename").click();
    cy.get("input[name=title]").type("foo");
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@code").should("contains.text", '"title": "Animals"');
    cy.get("@page").should("contains.text", "Animals");
  });

  it("submit", () => {
    cy.get("@code").should("contains.text", '"title": "Animals"');
    cy.get("@page").should("contains.text", "Animals");
    cy.get("@options").click();
    cy.get("@options").contains("Rename").click();
    cy.get("input[name=title]").type("foo");
    cy.get(".k-dialog-submit-button").click();
    cy.get("@code").should("contains.text", '"title": "foo"');
    cy.get("@page").should("contains.text", "foo");
  });
})

describe("PageStatusDialog", () => {
  beforeEach(prepare);

  it("cancel", () => {
    cy.get("@code").should("contains.text", '"status": "listed"');
    cy.get("@options").click();
    cy.get("@options").contains("Change status").click();
    cy.get(".k-radio-input label").first().click();
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@code").should("contains.text", '"status": "listed"');
  });

  it("submit as draft", () => {
    cy.get("@code").should("contains.text", '"status": "listed"');
    cy.get("@options").click();
    cy.get("@options").contains("Change status").click();
    cy.get(".k-radio-input label").first().click();
    cy.get(".k-dialog-submit-button").click();
    cy.get("@code").should("contains.text", '"status": "draft"');
  });

  it("submit as unlisted", () => {
    cy.get("@code").should("contains.text", '"status": "listed"');
    cy.get("@options").click();
    cy.get("@options").contains("Change status").click();
    cy.get(".k-radio-input label").eq(1).click();
    cy.get(".k-dialog-submit-button").click();
    cy.get("@code").should("contains.text", '"status": "unlisted"');
  });
})

describe("PageRemoveDialog", () => {
  beforeEach(prepare);

  it("cancel", () => {
    cy.get("@options").click();
    cy.get("@options").contains("Delete").click();
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@page").should("exist");
  });

  it("no check", () => {
    cy.get("@options").click();
    cy.get("@options").contains("Delete").click();
    cy.get(".k-notification[data-theme=error]").should("not.exist");
    cy.get(".k-dialog-submit-button").click();
    cy.get(".k-notification[data-theme=error]").should("exist");
  });

  it("invalid check", () => {
    cy.get("@options").click();
    cy.get("@options").contains("Delete").click();
    cy.get(".k-notification[data-theme=error]").should("not.exist");
    cy.get("input[name=check]").type("foo");
    cy.get(".k-dialog-submit-button").click();
    cy.get(".k-notification[data-theme=error]").should("exist");
  });

  it("submit", () => {
    cy.get("@options").click();
    cy.get("@options").contains("Delete").click();
    cy.get("input[name=check]").type("Animals");
    cy.get(".k-dialog-submit-button").click();
    cy.get("@pages").should("have.length", 0);
  });
})
