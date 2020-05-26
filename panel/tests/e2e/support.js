Cypress.Commands.add('loadStory', (component, story) => {
  component = component.replace(" | ", "-").replace(" / ", "-").replace(" ", "-").toLowerCase();
  story = story.toLowerCase();
  cy.visit(`iframe.html?id=${component}--${story}`);
});

Cypress.Commands.add('emitted', type => {
  const win = cy.state('window')
  return win.__actions
    .filter(action => action.data.name === type)
    .map(action => action.data.args);
});
