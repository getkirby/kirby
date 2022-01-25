const dialog = () => {
  return cy.get(".k-dialog");
};

const createBlock = (type) => {
  cy.get(".k-blocks-field").as("field");
  cy.get("@field").find(".k-blocks-empty").click();

  dialog().find("button").contains(type).click();

  cy.get("@field").find(".k-block").as("block");
};

describe("Blocks", () => {
  before(() => {
    cy.visit("/env/install/blocks");
    cy.visit("/env/user/test");
  });

  beforeEach(() => {
    cy.visit("/env/auth/test");
  });

  describe("List block", () => {
    beforeEach(() => {
      cy.visit("/panel/pages/home");
    });

    it("should be creatable", () => {
      createBlock("List");

      cy.get("@block").should("have.class", "k-block-type-list");
    });

    it("should create list items", () => {
      createBlock("List");

      cy.get("@block").find(".ProseMirror").as("editor");

      cy.get("@editor").type("List item 1{enter}List item 2");
      cy.get("@editor").find("ul").should("have.length", 1);
      cy.get("@editor").find("li").should("have.length", 2);
    });
  });
});
