import BrowserRoute from "./BrowserRoute.vue";

export default {
  title: "App | Routes / Browser ",
  component: BrowserRoute
};

export const regular = () => ({
  template: `
    <k-browser-route />
  `
});
