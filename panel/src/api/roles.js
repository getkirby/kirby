import Vue from "vue";

export default (api) => {
  return {
    async list(params) {
      return api.get("roles", params);
    },
    async get(name) {
      return api.get("roles/" + name);
    },
    async options(params) {
      const roles = await this.list(params);
      return roles.data.map(role => {
        return {
          info: role.description || `(${Vue.$t("role.description.placeholder")})`,
          text: role.title,
          value: role.name
        };
      });
    }
  }
};
