const dialog = () => {
  return cy.get('.k-dialog');
};

describe('PageView', () => {

  const host = 'http://localhost:8080';

  before(() => {
    cy.visit(host + '/env/install/starterkit');
    cy.visit(host + '/env/user/test');
  });

  beforeEach(() => {
    cy.visit(host + '/env/auth/test');
  });

  describe('Photography', () => {

    beforeEach(() => {
      cy.visit(host + '/pages/photography');
      cy.get('.k-section-name-drafts').as('drafts');
      cy.get('.k-section-name-listed').as('listed');
    });

    it('should display correctly', () => {
      // Title
      cy.get('.k-headline-editable').should('contain', 'Photography');
      cy.get('.k-topbar-crumbs a:last-child').should('contain', 'Photography');

      // Buttons
      cy.get('.k-header-buttons .k-button-group:first-child .k-button:first-child').then((openButton) => {
        openButton.should('have.attr', 'target', '_blank');
        openButton.should('have.attr', 'href', 'http://sandbox.test/photography');
      });

      cy.get('.k-header-buttons .k-button-group:first-child .k-status-flag').should('contain', 'Public');

      // Drafts
      cy.get('@drafts').find('.k-headline').should('contain', 'Drafts');

      // Published Albums
      cy.get('@listed').find('.k-headline').should('contain', 'Published Albums');
    });

    it('should create draft', () => {
      cy.get('@drafts').find('.k-section-header .k-button').click();

      dialog().find('input[name=title]').type('Portraits');
      dialog().find('form').submit();

      cy.url().should('contain', '/pages/photography+portraits');
      cy.get('.k-headline-editable').should('contain', 'Portraits');
      cy.get('.k-status-flag').should('contain', 'Draft');
    });

    it('should publish draft', () => {
      cy.get('@drafts').find('.k-card').should('have.length', 2);
      cy.get('@listed').find('.k-card').should('have.length', 8);

      cy.get('@drafts').find('.k-card:first-child .k-status-flag').click();

      dialog().find('.k-radio-input li:last-child label').click();
      dialog().find('form').submit();

      cy.get('@drafts').find('.k-card').should('have.length', 1);
      cy.get('@listed').find('.k-card').should('have.length', 9);
    });

    it('should delete draft', () => {
      cy.get('@drafts').as('draft');
      cy.get('@drafts').find('.k-card').should('have.length', 1);

      cy.get('@draft').find('.k-card-options-button:last-child').click();
      cy.get('@draft').find('.k-card-options-dropdown .k-button:last-child').click();

      dialog().find('.k-dialog-button-submit').click();

      cy.get('@drafts').find('.k-card').should('have.length', 0);
    });

  });

});
