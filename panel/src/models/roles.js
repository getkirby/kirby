import Vue from "vue";
import Api from "@/api/api.js";

export default {
  async options(params) {
    const roles = await Api.roles.list(params);
    return roles.data.map(role => {
      return {
        info: role.description || `(${Vue.i18n.translate("role.description.placeholder")})`,
        text: role.title,
        value: role.name
      };
    });
  }
}
