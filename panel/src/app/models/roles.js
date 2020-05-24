
export default (Vue, store) => ({
  async options(params) {
    const roles = await Vue.$api.roles.list(params);
    return roles.data.map(role => ({
      info: role.description || `(${Vue.$t("role.description.placeholder")})`,
      text: role.title,
      value: role.name
    }));
  }
});
