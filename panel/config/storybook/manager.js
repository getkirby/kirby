import { addons } from "@storybook/addons";
import theme from "./theme.js";
import "./theme.css";

addons.setConfig({
  theme: theme,
  showPanel: true,
  panelPosition: "bottom",
  previewTabs: {
  },
});
