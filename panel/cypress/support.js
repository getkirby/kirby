import 'cypress-file-upload';
import 'cypress-plugin-snapshots/commands';

Cypress.Commands.add('loadStory', (component, story) => {
  component = component.replace(/\s\|\s/g, "-").replace(/\s\/\s/g, "-").replace(/\s/g, "-").toLowerCase();
  story = story.replace(/ /g, "-").toLowerCase();
  cy.visit(`iframe.html?id=${component}--${story}`);

  // Reset storybook helpers
  const win = cy.state('window')
  win.__actions = [];
  win.__routed = [];
});

Cypress.Commands.add('emitted', type => {
  const win = cy.state('window')
  return win.__actions
    .filter(action => action.data.name === type)
    .map(action => action.data.args);
});

Cypress.Commands.add('routed', path => {
  const win = cy.state('window')
  return win.__routed
    .filter(route => route === path);
});
