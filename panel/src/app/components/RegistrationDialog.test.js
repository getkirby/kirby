
describe("RegistrationDialog", () => {
  beforeEach(() => {
    cy.loadStory("App | Dialogs / Registration Dialog", "Regular");
  });

  // TODO: move the first two to Dialog test
  it("opens", () => {
    cy.get(".k-dialog").should("not.exist");
    cy.contains("Open").click();
    cy.get(".k-dialog").should("exist");
  });

  it("can be closed", () => {
    cy.contains("Open").click();
    cy.get(".k-dialog").should("exist");
    cy.contains("Cancel").click();
    cy.get(".k-dialog").should("not.exist");
  });

  it("is empty on (re-)open", () => {
    cy.contains("Open").click();
    cy.get("input[name=license]").should("be.empty");
    cy.get("input[name=email]").should("be.empty");
    cy.get("input[name=license]").type("foo");
    cy.get("input[name=email]").type("homer@simpson.de");
    cy.contains("Cancel").click();
    cy.contains("Open").click();
    cy.get("input[name=license]").should("be.empty");
    cy.get("input[name=email]").should("be.empty");
  });

  it("@success on submit", () => {
    cy.contains("Open").click();
    cy.get("input[name=license]").type("K3-test");
    cy.get("input[name=email]").type("homer@simpson.de");
    cy.emitted("success").should("be.empty");
    cy.contains("Register").click();
    cy.wait(500);
    cy.emitted("success").should("not.be.empty");
  });

  it("error if licsense wrong", () => {
    cy.contains("Open").click();
    cy.get("input[name=license]").type("foo");
    cy.get("input[name=email]").type("homer@simpson.de");
    cy.contains("Invalid license key").should("not.exist");
    cy.contains("Register").click();
    cy.contains("Invalid license key").should("exist");
  });
})
