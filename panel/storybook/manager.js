import { addons } from "@storybook/addons";
import theme from "./theme/theme.js";
import "./theme/theme.css";

addons.setConfig({
  theme: theme,
  showPanel: true,
  panelPosition: "bottom"
});
