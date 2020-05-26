
describe("RegistrationDialog", () => {
  beforeEach(() => {
    cy.loadStory("App | Dialogs / Registration Dialog", "Regular");
    cy.contains("Register Kirby").as("button");
  });

  // TODO: move the first two to Dialog test
  it("opens", () => {
    cy.get(".k-dialog").should("not.exist");
    cy.get("@button").click();
    cy.get(".k-dialog").should("exist");
  });

  it("can be canceled", () => {
    cy.get("@button").click();
    cy.get(".k-dialog").should("exist");
    cy.get(".k-dialog-cancel-button").click();
    cy.get(".k-dialog").should("not.exist");
  });

  it("resets when canceled", () => {
    cy.get("@button").click();
    cy.get("input[name=license]").type("foo");
    cy.get("input[name=email]").type("homer@simpson.de");
    cy.get(".k-dialog-cancel-button").click();
    cy.get("@button").click();
    cy.get("input[name=license]").should("be.empty");
    cy.get("input[name=email]").should("be.empty");
  });

  it("@success on submit", () => {
    cy.get("@button").click();
    cy.get("input[name=license]").type("K3-test");
    cy.get("input[name=email]").type("homer@simpson.de");
    cy.emitted("success").should("be.empty");
    cy.get(".k-dialog-submit-button").click();
    cy.wait(500);
    cy.emitted("success").should("not.be.empty");
  });

  it("invalid license", () => {
    cy.get("@button").click();
    cy.get("input[name=license]").type("foo");
    cy.get("input[name=email]").type("homer@simpson.de");
    cy.get(".k-notification[data-theme=error]").should("not.exist");
    cy.get(".k-dialog-submit-button").click();
    cy.get(".k-notification[data-theme=error]").should("exist");
  });
})
