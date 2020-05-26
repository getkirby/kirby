Cypress.Commands.add('loadStory', (component, story) => {
  component = component.replace(" | ", "-").replace(" / ", "-").replace(" ", "-").toLowerCase();
  story = story.replace(" ", "-").toLowerCase();
  cy.visit(`iframe.html?id=${component}--${story}`);
});

Cypress.Commands.add('emitted', type => {
  const win = cy.state('window')
  return win.__actions
    .filter(action => action.data.name === type)
    .map(action => action.data.args);
});

Cypress.Commands.add('expectRoutingTo', path => {
  window.__routed = false;
  cy.on('window:alert', (str) => {
    expect(str).to.equal(`$router.push('${path}')`);
    window.__routed = true;
  });

});

Cypress.Commands.add('wasRouted', path => {
  cy.wrap(null).should(() => {
    expect( window.__routed).to.be.true
  })
});
