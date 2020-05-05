
export default function (Vue) {
  return {
    async options(params) {
      const roles = await Vue.prototype.$api.roles.list(params);
      return roles.data.map(role => {
        return {
          info: role.description || `(${Vue.i18n.translate("role.description.placeholder")})`,
          text: role.title,
          value: role.name
        };
      });
    }
  };
}
