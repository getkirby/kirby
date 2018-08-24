export default {
  install(Vue) {
    Vue.prototype.$cache = {
      id(route, store) {
        if (route.name === "Account") {
          return '/users/' + store.state.user.current.id + route.hash;
        }

        return route.path + route.hash;
      },
      items() {
        let items = {...localStorage};
        Object.keys(items).forEach((key) => {
          if (key.startsWith('kirby$')) {
            items[key.replace('kirby$', '')] = JSON.parse(items[key]);
          }
          delete items[key];
        });
        return items;
      },
      exists(id) {
        return this.get(id) !== null;
      },
      get(id) {
        let values = localStorage.getItem('kirby$' + id);
        return values && JSON.parse(values);
      },
      set(id, values) {
        values = Object.assign({}, this.get(id) || {}, values);
        localStorage.setItem('kirby$' + id, JSON.stringify(values));
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
        if (Object.keys(values).length > 0) {
          localStorage.setItem('kirby$' + id, JSON.stringify(values));
        } else {
          this.remove(id);
        }
      },
      field(id, field, value) {
        let values = this.get(id) || {};
        values[field] = value;
        this.set(id, values);
      },
      remove(id) {
        localStorage.removeItem('kirby$' + id);
      }
    };
  }
};
