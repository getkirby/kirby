Cypress.Commands.add('visitStorybook', () => {
  return cy.visit('iframe.html')
})

Cypress.Commands.add('loadStory', (component, story) => {
  const log = Cypress.log({
    name: 'Load',
    message: [component, story],
    $el: Cypress.$('#root')
  })
  log.snapshot('before')

  const win = cy.state('window')
  const now = performance.now()
  win.__setStory(
    component.replace(/[|/]/g, '-').toLowerCase(),
    story.replace(/\s/g, '-').replace(/([a-z0-9]|(?=[A-Z]))([A-Z])/g, '$1-$2').toLowerCase()
  )
  log.set('consoleProps', () => ({
    component,
    story,
    renderTime: performance.now() - now
  }))
  log.snapshot('after')
  log.end()

  return Cypress.$('#root')
});

Cypress.Commands.add('emitted', (type) => {
  const win = cy.state('window')
  return win.__actions
    .filter(action => action.data.name === type)
    .map(action => action.data.args);
});
