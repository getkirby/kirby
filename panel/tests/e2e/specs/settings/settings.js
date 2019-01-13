
describe("Settings", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.login("admin");
  });

  it("visits settings view", () => {
    cy.visit("/settings");
    cy.get(".k-headline").should("contain", "Settings");
  });

});
