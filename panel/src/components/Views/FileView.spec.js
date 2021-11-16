const dialog = () => {
  return cy.get(".k-dialog");
};

describe("FileView", () => {
  beforeEach(() => {
    cy.visit("/env/install/starterkit");
    cy.visit("/env/user/test");
    cy.visit("/env/auth/test");
  });

  describe("Page File", () => {
    beforeEach(() => {
      cy.visit("/panel/pages/photography+trees/files/cheesy-autumn.jpg");
    });

    it("should display correctly", () => {
      // Title
      cy.get(".k-headline-editable").should("contain", "cheesy-autumn.jpg");
      cy.get(".k-topbar-breadcrumb a:last-child").should(
        "contain",
        "cheesy-autumn.jpg"
      );

      // Info
      cy.get(".k-file-preview-details").as("info");

      cy.get("@info").find("li:nth-child(1)").should("contain", "Template");
      cy.get("@info").find("li:nth-child(1)").should("contain", "image");

      cy.get("@info").find("li:nth-child(2)").should("contain", "Media Type");
      cy.get("@info").find("li:nth-child(2)").should("contain", "image/jpeg");

      cy.get("@info").find("li:nth-child(3)").should("contain", "Url");
      cy.get("@info")
        .find("li:nth-child(3)")
        .should("contain", "/photography/trees/cheesy-autumn.jpg");

      cy.get("@info").find("li:nth-child(4)").should("contain", "Size");
      cy.get("@info").find("li:nth-child(4)").should("contain", "KB");

      cy.get("@info").find("li:nth-child(5)").should("contain", "Dimensions");
      cy.get("@info")
        .find("li:nth-child(5)")
        .should("contain", "933 × 1400 Pixel");

      cy.get("@info").find("li:nth-child(6)").should("contain", "Orientation");
      cy.get("@info").find("li:nth-child(6)").should("contain", "Portrait");

      // Preview Button
      cy.get(
        '.k-header [data-position="left"] > .k-button-group > :nth-child(1)'
      ).as("preview");

      cy.get("@preview").should("have.attr", "target", "_blank");
      cy.get("@preview")
        .invoke("attr", "href")
        .should(
          "contain",
          Cypress.config().baseUrl + "/photography/trees/cheesy-autumn.jpg"
        );
    });

    it("should be renamed", () => {
      // open settings
      cy.get(".k-headline-editable").click();

      dialog().find('input[name="name"]').type("trees");
      dialog().find("form").submit();

      cy.url().should("contain", "/pages/photography+trees/files/trees.jpg");
    });

    it("should be deleted", () => {
      // open settings
      cy.get(
        ".k-header [data-position=left] .k-button-group:first-child :nth-child(2) .k-button"
      ).click();
      cy.get(
        ".k-header [data-position=left] .k-dropdown-content .k-button:last-child"
      ).click();

      dialog().find(".k-dialog-button-submit").click();
      cy.url().should(
        "eq",
        Cypress.config().baseUrl + "/panel/pages/photography+trees"
      );
    });
  });
});
