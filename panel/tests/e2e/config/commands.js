// ***********************************************
// https://on.cypress.io/custom-commands
// ***********************************************

Cypress.Commands.add('login', (type) => {

    cy.fixture('users').then(users => {
      return users[type];
    }).then(user => {
      cy.request({
        url: '/api/auth',
        method: 'POST',
        body: {
          email: user.email,
          password: user.password
        }
      }).then(auth => {
        localStorage.setItem("auth", auth.body.token);
      });
    });

  })
