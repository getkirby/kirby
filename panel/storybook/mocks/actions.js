import addons from '@storybook/addons'
import { EVENT_ID } from '@storybook/addon-actions'

// Collect actions emitted by storybook/addon-actions
// for Cypress
window.__actions = [];
addons.getChannel().addListener(EVENT_ID, args => {
  window.__actions.push(args);
});
