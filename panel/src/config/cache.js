export default {
  install(Vue) {
    Vue.prototype.$cache = {
      exists(id) {
        return this.get(id) !== null;
      },
      get(id) {
        let values = localStorage.getItem(id);
        return values && JSON.parse(values);
      },
      set(id, values) {
        values = Object.assign({}, this.get(id) || {}, values);
        localStorage.setItem(id, JSON.stringify(values));
      },
      field(id, field, value) {
        let values = this.get(id) || {};
        values[field] = value;
        this.set(id, values);
      },
      remove(id) {
        localStorage.removeItem(id);
      }
    };
  }
};
