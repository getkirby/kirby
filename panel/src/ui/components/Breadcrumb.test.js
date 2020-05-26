import stories from "./Breadcrumb.stories.js";

describe("Breadcrumb - simple", () => {
  beforeEach(() => {
    cy.loadStory(stories.title, "Simple");
  });

  it("has crumbs", () => {
    cy.get(".k-breadcrumb li")
      .first().should("contain.text", "Home")
      .next().should("contain.text", "Docs")
      .next().should("contain.text", "Guide")
      .next().should("contain.text", "Blueprints");
  });

  it("routes when clicked", () => {
    cy.routed("https://getkirby.com").should("be.empty");
    cy.get(".k-breadcrumb li").first().click();
    cy.routed("https://getkirby.com").should("not.be.empty");
  });
})

describe("Breadcrumb - with icon", () => {
  beforeEach(() => {
    cy.loadStory(stories.title, "with icon");
  });

  it("has icon", () => {
    cy.get(".k-breadcrumb-icon").should("exist").and("have.class", "k-icon-home");
  });
})
