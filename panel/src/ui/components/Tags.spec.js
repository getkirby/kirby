import stories from "./Tags.stories.js";

describe("Tags - regular", () => {
  before(() => {
    cy.visitStorybook();
  });

  beforeEach(() => {
    cy.loadStory(stories.title, "Regular");
  });

  it("has 2 tags", () => {
    cy.get(".k-tag").should("has.length", 2);
    cy.contains("Design");
    cy.contains("Photography");
  });

  it("has toggle for each", () => {
    cy.get(".k-tag-toggle").should("has.length", 2);
  });

  it("removed when toggle clicked", () => {
    cy.get(".k-tag-toggle").first().click();
    cy.get(".k-tag").should("has.length", 1);
    cy.contains("Photography");
  });
})
