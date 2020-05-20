export default (api) => {
  return {
    async get(code) {
      return await api.get("languages/" + code);
    },
    async delete(code) {
      return api.delete("languages/" + code);
    },
    async list() {
      return await api.get("languages");
    },
  }
};
