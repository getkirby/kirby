import stories from "./Button.stories.js";

const checkClick = () => {
  cy.emitted("click").should("be.empty");
  cy.get("@button").click();
  cy.emitted("click").should("have.length", 1);
  cy.get("@button").click();
  cy.emitted("click").should("have.length", 2);
};

describe("Button - only text", () => {
  before(() => {
    cy.loadStory(stories.title, "Only Text");
  });

  beforeEach(() => {
    cy.get(".k-button").as("button");
  });

  it("has text", () => {
    cy.get("@button").should("contain.text", "Text Button");
  });

  it("has no icon", () => {
    cy.get("@button").find(".k-icon").should("not.exist");
  });

  it("emits when clicked", checkClick);
})

describe("Button - text and icon", () => {
  before(() => {
    cy.loadStory(stories.title, "Text and Icon");
  });

  beforeEach(() => {
    cy.get(".k-button").as("button");
  });

  it("has text", () => {
    cy.get("@button").should("contain.text", "Icon & Text");
  });

  it("has icon", () => {
    cy.get("@button").find(".k-icon").should("exist");
    cy.get("@button").find(".k-icon-edit").should("exist");
  });

  it("emits when clicked", checkClick);
})

describe("Button - only icon", () => {
  before(() => {
    cy.loadStory(stories.title, "Only Icon");
  });

  beforeEach(() => {
    cy.get(".k-button").as("button");
  });

  it("has no text", () => {
    cy.get("@button").should("not.contain.text");
  });

  it("has icon", () => {
    cy.get("@button").find(".k-icon").should("exist");
    cy.get("@button").find(".k-icon-edit").should("exist");
  });

  it("emits when clicked", checkClick);
})

describe("Button - link", () => {
  before(() => {
    cy.loadStory(stories.title, "Link");
  });

  beforeEach(() => {
    cy.get(".k-button").as("button");
  });

  it("routes when clicked", () => {
    cy.routed("https://getkirby.com").should("be.empty")
    cy.get("@button").click();
    cy.routed("https://getkirby.com").should("not.be.empty")
  });
})

describe("Button - disabled", () => {
  before(() => {
    cy.loadStory(stories.title, "Disabled");
  });

  beforeEach(() => {
    cy.get(".k-button").as("button");
  });

  it("nothing when clicked", () => {
    cy.emitted("click").should("be.empty");
    cy.routed("https://getkirby.com").should("be.empty")
    cy.get("@button").click();
    cy.emitted("click").should("be.empty");
    cy.routed("https://getkirby.com").should("be.empty")
  });
})

describe("Button - text as prop", () => {
  before(() => {
    cy.loadStory(stories.title, "Text As Prop");
    cy.get(".k-button").as("button");
  });

  it("has text", () => {
    cy.get("@button").should("contain.text", "Text Button");
  });
})

describe("Button - text false", () => {
  before(() => {
    cy.loadStory(stories.title, "Text False");
    cy.get(".k-button").as("button");
  });

  it("is empty", () => {
    cy.get("@button").should("be.empty");
  });
})
