describe('SiteView', () => {

  const host = 'http://localhost:8080';

  before(() => {
    cy.visit(host + '/env/install/minimal');
    cy.visit(host + '/env/user/test');
  });

  beforeEach(() => {
    cy.visit(host + '/env/auth/test');
  });

  it('should redirect to /site', () => {
    cy.visit(host);
    cy.url().should('include', '/site');
  });

  it('should be active in menu', () => {
    cy.visit(host + '/site');
    cy.get('.k-topbar-menu-button').click();
    cy.get('.k-topbar-menu li:first-child').should('have.attr', 'aria-current', 'true');
  });

  it('should have site title', () => {
    cy.visit(host + '/site');
    cy.get('.k-headline-editable').should('contain', 'Test');
    cy.get('.k-topbar-view-button').should('contain', 'Test');
  });

  it('should update site title', () => {
    cy.visit(host + '/site');
    cy.get('.k-headline-editable').click();
    cy.get('.k-dialog input[name="title"]').type('My Site');
    cy.get('.k-dialog form').submit();
    cy.get('.k-headline-editable').should('contain', 'My Site');
    cy.get('.k-topbar-view-button').should('contain', 'My Site');
  });

  it('should have working preview button', () => {
    cy.visit(host + '/site');
    const button = cy.get('.k-header-buttons .k-button');
    button.should('have.attr', 'target', '_blank');
    button.should('have.attr', 'href', 'http://sandbox.test');
  });
});
