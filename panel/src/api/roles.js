import Vue from "vue";
import api from "./api.js";

export default {
  list() {
    return api.get("roles");
  },
  get(name) {
    return api.get("roles/" + name);
  },
  options() {
    return this.list().then(roles => {
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
