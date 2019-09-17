import Vue from "vue";
import api from "./api.js";

export default {
  list(params) {
    return api.get("roles", params);
  },
  get(name) {
    return api.get("roles/" + name);
  },
  options(params) {
    return this.list(params).then(roles => {
      return roles.data.map(role => {
        return {
          info: role.description || `(${Vue.i18n.translate("role.description.placeholder")})`,
          text: role.title,
          value: role.name
        };
      });
    });
  }
};
