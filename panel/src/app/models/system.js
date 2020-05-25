export default (Vue, store) => ({
  async register(registration) {
    await Vue.$api.system.register(registration);
    await store.dispatch("system/register", registration.license);
  }
});
