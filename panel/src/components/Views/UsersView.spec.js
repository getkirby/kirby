describe('UsersView', () => {

  before(() => {
    cy.visit('/env/install/roles');
    cy.visit('/env/user/test');
  });

  beforeEach(() => {
    cy.visit('/env/auth/test');
    cy.visit('/users');
    cy.get('.k-users-view .k-collection').as('users');
  });

  it('should display correctly', () => {
    cy.get('.k-topbar-menu-button').click();
    cy.get('.k-topbar-menu li:nth-child(2)').should('have.attr', 'aria-current', 'true');

    cy.get('.k-headline').should('contain', 'Users');
    cy.get('.k-topbar-view-button').should('contain', 'Users');

    cy.get('@users').should('have.attr', 'data-layout', 'list');
    cy.get('@users').find('li').should('have.length', 1);
    cy.get('@users').find('li:first-child').should('contain', 'test@getkirby.com');
    cy.get('@users').find('li:first-child').should('contain', 'Admin');
  });

  describe('UserCreateDialog', () => {

    beforeEach(() => {
      cy.get('.k-header-buttons .k-button-group:first-child > .k-button:first-child').click();
      cy.get('.k-dialog').as('dialog');
    });

    it('should create admin', () => {
      cy.get('@dialog').find('input[name="name"]').type('Ada');
      cy.get('@dialog').find('input[name="email"]').type('ada@getkirby.com');
      cy.get('@dialog').find('input[name="password"]').type('top-secret-1234');
      cy.get('@dialog').find('form').submit();

      cy.get('@users').find('li').should('have.length', 2);
      cy.get('@users').find('li:nth-child(1)').should('contain', 'Ada');
      cy.get('@users').find('li:nth-child(1)').should('contain', 'Admin');
    });

    it('should create editor', () => {
      cy.get('@dialog').find('input[name="name"]').type('Grace');
      cy.get('@dialog').find('input[name="email"]').type('grace@getkirby.com');
      cy.get('@dialog').find('input[name="password"]').type('top-secret-1234');
      cy.get('@dialog').find('.k-radio-input li:last-child label').click();
      cy.get('@dialog').find('form').submit();

      cy.get('@users').find('li').should('have.length', 3);
      cy.get('@users').find('li:nth-child(2)').should('contain', 'Grace');
      cy.get('@users').find('li:nth-child(2)').should('contain', 'Editor');
    });

  });

});
