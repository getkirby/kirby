import { addons } from "@storybook/addons";
import theme from "./theme.js";

addons.setConfig({
  theme: theme,
  showPanel: true,
  panelPosition: "right",
  previewTabs: {
    canvas: null,
    'storybook/docs/panel': 'MDX'
  },
});

setTimeout(function() {
  addons.elements.panel["storybookjs/knobs/panel"].title = "Props";
  addons.elements.panel["storybook/actions/panel"].title = "Events";
}, 0);
