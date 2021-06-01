describe('UsersView', () => {

  before(() => {
    cy.visit('/env/install/roles');
    cy.visit('/env/user/test');
  });

  beforeEach(() => {
    cy.visit('/env/auth/test');
    cy.visit('/panel/users');
    cy.get('.k-users-view .k-collection').as('users');
  });

  it('should display correctly', () => {
    cy.get('.k-topbar-menu-button').click();
    cy.get('.k-topbar-menu li:nth-child(3)').should('have.attr', 'aria-current', 'true');

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
      // @todo the order of the users in the list is non-deterministic;
      // cannot reliably test the order and if the role is in the same line as the name
      cy.get('@users').find('li.k-list-item').should('contain', 'Ada');
      cy.get('@users').find('li.k-list-item').should('contain', 'Admin');
    });

    it('should create editor', () => {
      cy.get('@dialog').find('input[name="name"]').type('Grace');
      cy.get('@dialog').find('input[name="email"]').type('grace@getkirby.com');
      cy.get('@dialog').find('input[name="password"]').type('top-secret-1234');
      cy.get('@dialog').find('.k-radio-input li:last-child label').click();
      cy.get('@dialog').find('form').submit();

      cy.get('@users').find('li').should('have.length', 3);
      // @todo the order of the users in the list is non-deterministic;
      // cannot reliably test the order and if the role is in the same line as the name
      cy.get('@users').find('li.k-list-item').should('contain', 'Grace');
      cy.get('@users').find('li.k-list-item').should('contain', 'Editor');
    });

  });

});
