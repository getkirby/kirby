// ***********************************************
// https://on.cypress.io/custom-commands
// ***********************************************

Cypress.Commands.add('login', (type) => {

    cy.fixture('users').then(users => {
      return users[type];
    }).then(user => {
      cy.request({
        url: '/api/auth/login',
        method: 'POST',
        auth: {
          username: user.email,
          password: user.password
        }
      })
    });

  })
