/* global process */

const panel = window.panel || {};
const defaults = {
  assets: "@/assets",
  api: "/api",
  site: process.env.VUE_APP_DEV_SERVER,
  url: "/",
  debug: true,
  translation: "en",
  search: {
    limit: 10
  }
};

export default { ...defaults, ...panel };
