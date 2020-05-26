import stories from "./Tag.stories.js";

describe("Tag - regular", () => {
  beforeEach(() => {
    cy.loadStory(stories.title, "Regular");
  });

  it("has text", () => {
    cy.get(".k-tag").should("have.text", "Tag");
    cy.get('.k-tag').toMatchSnapshot();
    cy.get('.k-tag').toMatchImageSnapshot();
  });

  it("has no toggle", () => {
    cy.get(".k-tag-toggle").should("not.exist");
  });
})

describe("Tag - removable", () => {
  beforeEach(() => {
    cy.loadStory(stories.title, "Removable");
  });

  it("has text", () => {
    cy.get(".k-tag").should("have.text", "Tag");
    cy.get('.k-tag').toMatchSnapshot();
    cy.get('.k-tag').toMatchImageSnapshot();
  });

  it("has toggle", () => {
    cy.get(".k-tag-toggle").should("exist");
  });

  it("@remove when toggle clicked", () => {
    cy.get(".k-tag-toggle").click();
    cy.emitted("remove").should("not.be.empty");
  });
})
