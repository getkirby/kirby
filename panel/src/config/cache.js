export default {
  install(Vue) {
    Vue.prototype.$cache = {
      id(route, store) {
        if (route.name === "Account") {
          return '/users/' + store.state.user.current.id + route.hash;
        }

        return route.path + route.hash;
      },
      all() {
        let all = {...localStorage};
        Object.keys(all).forEach((key) => {
          if (key.startsWith('kirby$')) {
            all[key.replace('kirby$', '')] = JSON.parse(all[key]);
          }
          delete all[key];
        });
        return all;
      },
      empty() {
        console.log(this.all());
        return Object.keys(this.all()).length === 0;
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
        this.count++;
      },
      field(id, field, value) {
        let values = this.get(id) || {};
        values[field] = value;
        this.set(id, values);
      },
      remove(id) {
        localStorage.removeItem('kirby$' + id);
        this.count--;
      }
    };
  }
};
