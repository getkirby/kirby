import Vue from "vue";

export default {
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
