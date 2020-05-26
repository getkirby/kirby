export default (api) => {
  return {
    async create(values) {
      return await api.post("languages", values);
    },
    async delete(code) {
      return api.delete("languages/" + code);
    },
    async get(code) {
      return await api.get("languages/" + code);
    },
    async list() {
      return await api.get("languages");
    },
    async update(code, values) {
      return api.patch("languages/" + code, values);
    },
  };
};
