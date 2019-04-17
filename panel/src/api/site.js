import Vue from "vue";
import api from "@getkirby/api-js";

export default {
  options() {
    return api.get("site", { select: "options" }).then(site => {
      const options = site.options;
      let result = [];
      result.push({
        click: "rename",
        icon: "title",
        text: Vue.i18n.translate("rename"),
        disabled: !options.changeTitle
      });
      return result;
    });
  }
};
