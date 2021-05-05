const panel = window.panel || {};
const defaults = {
  assets: "@/assets",
  api: "/api",
  site: import.meta.env.BASE_URL,
  url: "/",
  debug: true,
  translation: "en",
  search: {
    limit: 10
  }
};

export default { ...defaults, ...panel };
