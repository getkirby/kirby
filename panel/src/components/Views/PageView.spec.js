const dialog = () => {
  return cy.get(".k-dialog");
};

describe("PageView", () => {
  before(() => {
    cy.visit("/env/install/starterkit");
    cy.visit("/env/user/test");
  });

  beforeEach(() => {
    cy.visit("/env/auth/test");
  });

  describe("Photography", () => {
    beforeEach(() => {
      cy.visit("/panel/pages/photography");
      cy.get(".k-section-name-drafts").as("drafts");
      cy.get(".k-section-name-listed").as("listed");
    });

    it("should display correctly", () => {
      // Title
      cy.get(".k-headline-editable").should("contain", "Photography");
      cy.get(".k-topbar-breadcrumb a:last-child").should(
        "contain",
        "Photography"
      );

      // Buttons
      cy.get(
        ".k-header-buttons .k-button-group:first-child .k-button:first-child"
      ).as("button");
      cy.get("@button").should("have.attr", "target", "_blank");
      cy.get("@button").should(
        "have.attr",
        "href",
        Cypress.config().baseUrl + "/photography"
      );

      cy.get(
        ".k-header-buttons .k-button-group:first-child .k-status-icon"
      ).should("contain", "Public");

      // Drafts
      cy.get("@drafts").find(".k-headline").should("contain", "Drafts");

      // Published Albums
      cy.get("@listed")
        .find(".k-headline")
        .should("contain", "Published Albums");
    });

    it("should create draft", () => {
      cy.get("@drafts").find(".k-section-header .k-button").click();

      dialog().find("input[name=title]").type("Portraits");
      dialog().find("form").submit();

      cy.url().should("contain", "/pages/photography+portraits");
      cy.get(".k-headline-editable").should("contain", "Portraits");
      cy.get(".k-status-icon").should("contain", "Draft");
    });

    it("should publish draft", () => {
      cy.get("@drafts").find(".k-cards-item").should("have.length", 2);
      cy.get("@listed").find(".k-cards-item").should("have.length", 8);

      cy.get("@drafts")
        .find(".k-cards-item:first-child .k-status-icon")
        .click();

      dialog().find(".k-radio-input li:last-child label").click();
      dialog().find("form").submit();

      cy.get("@drafts").find(".k-cards-item").should("have.length", 1);
      cy.get("@listed").find(".k-cards-item").should("have.length", 9);
    });

    it("should delete draft", () => {
      cy.get("@drafts").as("draft");
      cy.get("@drafts").find(".k-cards-item").should("have.length", 1);

      cy.get("@draft").find(".k-item-options-dropdown .k-button").click();
      cy.get("@draft")
        .find(".k-options-dropdown-content .k-button:last-child")
        .click();

      dialog().find(".k-dialog-button-submit").click();

      cy.get("@drafts").find(".k-card").should("have.length", 0);
    });
  });
});
