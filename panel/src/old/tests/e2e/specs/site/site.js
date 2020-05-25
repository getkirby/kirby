
describe("Site", () => {

  before(() => {
    cy.exec("npm run testkit:reset");
    cy.install();
  });

  beforeEach(() => {
    cy.login("admin");
  });

  it("visits site view", () => {
    cy.visit("/site");
    cy.get(".k-headline").should("contain", "Testkit");
  });

});
