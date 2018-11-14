// ***********************************************
// https://on.cypress.io/custom-commands
// ***********************************************

Cypress.Commands.add("login", (type) => {

    cy.fixture("users").then(users => {
      return users[type];
    }).then(user => {
      cy.request({
        url: "/api/auth/login",
        method: "POST",
        headers: {
          "x-csrf": "dev"
        },
        body: {
          email: user.email,
          password: user.password
        }
      })
    });

})

Cypress.Commands.add('install', () => {

  cy.fixture("users").then(users => {
    return users["admin"];
  }).then(user => {
    cy.request({
      url: "/api/system/install",
      method: "POST",
      headers: {
        "x-csrf": "dev"
      },
      body: {
        email: user.email,
        password: user.password,
        role: "admin",
        language: "en"
      }
    });
  });

});


Cypress.Commands.add('createUser', (role) => {
  cy.fixture("users").then(users => {
    return users[role];
  }).then(user => {
    cy.request({
      url: "/api/users",
      method: "POST",
      headers: {
        "x-csrf": "dev"
      },
      body: {
        email: user.email,
        password: user.password,
        role: role,
        language: "en"
      }
    });
  });

});

