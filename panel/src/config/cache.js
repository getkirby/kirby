export default {
  install(Vue) {
    Vue.prototype.$cache = {
      id(route, store) {
        if (route.name === "Account") {
          return '/users/' + store.state.user.current.id + route.hash;
        }

        return route.path + route.hash;
      },
      exists(id) {
        let cache = this.get(id);
        return cache !== null && Object.keys(cache).length > 0;
      },
      get(id) {
        let values = localStorage.getItem(id);
        return values && JSON.parse(values);
      },
      set(id, values) {
        values = Object.assign({}, this.get(id) || {}, values);
        localStorage.setItem(id, JSON.stringify(values));
      },
      unset(id, fields) {
        // get stored values from localStorage
        let values = Object.assign({}, this.get(id));

        // unset provided fields from values
        Object.keys(values).forEach((field) => {
          if(fields.indexOf(field) !== -1) {
            delete values[field];
          }
        });

        // set localStorage without the removed fields
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
