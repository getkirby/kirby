import { forceReRender } from '@storybook/vue'
import addons from '@storybook/addons'
import Events from '@storybook/core-events'
import { toId } from '@storybook/csf'
import { EVENT_ID } from '@storybook/addon-actions'
import ReactDOM from 'react-dom';

const clearStory = () => {
  const root = document.querySelector('#root')
  ReactDOM.unmountComponentAtNode(root)

  // Also reset logged actions.
  window.__actions = [];
}

window.__setStory = (component, story) => {
  clearStory()
  addons.getChannel().emit(Events.SET_CURRENT_STORY, {
    storyId: toId(component, story)
  });
  forceReRender();
};

// Collect actions emitted by storybook/addon-actions
window.__actions = [];

addons.getChannel().addListener(EVENT_ID, args => {
  window.__actions.push(args);
});
