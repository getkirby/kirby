import Vue from "vue";
import api from "./api.js";

export default {
  get(query) {
    return api.get("site", query);
  },
  update(data) {
    return api.post("site", data);
  },
  title(title) {
    return api.patch("site/title", { title: title });
  },
  options() {
    return api.get("site/options").then(options => {
      let result = [];

      result.push({
        click: "rename",
        icon: "title",
        text: Vue.i18n.translate("rename"),
        disabled: !options.changeTitle
      });

      result.push({
        click: "languages",
        icon: "globe",
        text: "Languages", // Vue.i18n.translate("languages"),
        disabled: false, // !options.changeLanguage
      });

      return result;
    });
  },
  children(query) {
    return api.post("site/children/search", query);
  },
  blueprint() {
    return api.get("site/blueprint");
  },
  blueprints() {
    return api.get("site/blueprints");
  }
};
