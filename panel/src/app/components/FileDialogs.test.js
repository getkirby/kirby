import stories from "./FileDialogs.stories.js";

const prepare = () => {
  cy.loadStory(stories.title, "Regular");
  cy.get("[data-cy=files]").children().as("files");
  cy.get("@files").first().as("file");
  cy.get("@file").find(".k-options-dropdown").as("options");
};

describe("FileDialogs", () => {
  beforeEach(prepare);

  it("check story", () => {
    cy.get("@files").should("exist");
    cy.get("@files").should("have.length", 1);
    cy.get("@file").should("exist");
  });

  it("upload file", () => {
    cy.get(".k-header-bar button").as("upload");
    cy.get("@files").should("have.length", 1);
    cy.get("@upload").should("exist");
    cy.get("@upload").click();
    cy.get("input[type=file]").attachFile("abba.jpg")
    cy.get("@file").next().should("exist");
    cy.get("@file").next().should("contains.text", "abba.jpg");
  });
})

describe("FileRenameDialog", () => {
  beforeEach(prepare);

  it("cancel", () => {
    cy.get("@file").should("contains.text", "free-wheely.jpg");
    cy.get("@options").click();
    cy.get("@options").contains("Rename file").click();
    cy.get("input[name=name]").type("foo");
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@file").should("contains.text", "free-wheely.jpg");
  });

  it("submit", () => {
    cy.get("@file").should("contains.text", "free-wheely.jpg");
    cy.get("@options").click();
    cy.get("@options").contains("Rename file").click();
    cy.get("input[name=name]").type("foo");
    cy.get(".k-dialog-submit-button").click();
    cy.get("@files").should("contains.text", "foo.jpg");
  });
})

describe("FileRemoveDialog", () => {
  beforeEach(prepare);

  it("cancel", () => {
    cy.get("@options").click();
    cy.get("@options").contains("Delete file").click();
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@file").should("exist");
  });

  it("submit", () => {
    cy.get("@options").click();
    cy.get("@options").contains("Delete file").click();
    cy.get(".k-dialog-submit-button").click();
    cy.get("@files").should("not.exist");
  });
})
