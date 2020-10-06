describe('InstallationView', () => {

  const host = 'http://localhost:8080';

  before(() => {
    cy.visit(host + '/env/install/minimal');
  });

  beforeEach(() => {
    cy.visit(host);
  });

  it('should fail', () => {
    cy.get('input[type="email"]').type("test@getkirby.com");
    cy.get('form').submit();
    cy.get('.k-dialog').should('contain', 'Please enter a valid password');
  });

  it('should install and redirect to SiteView', () => {
    cy.get('input[type="email"]').type("test@getkirby.com");
    cy.get('input[type="password"]').type("super-secure-1234");
    cy.get('form').submit();

    cy.url().should('include', '/site');
  });

});
