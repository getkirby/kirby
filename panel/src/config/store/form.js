import Vue from "vue";
import Api from "@/api/api.js";
import clone from "@/ui/helpers/clone.js";

export default {
  namespaced: true,
  state: {
    models: {}
  },
  getters: {
    changes: (state, getters) => (id) => {
      return getters.data('changes', id);
    },
    content: (state, getters) => (id) => {
      return getters.model(id).content;
    },
    data: (state, getters) => (type, id) => {
      // data type for specific model
      if (id) {
        return getters.model(id)[type];
      }

      // data type from all models
      let items = Object.assign({}, state.models);

      Object.keys(items).forEach(key => {
        items[key] = items[key][type];
        if (Object.keys(items[key]).length === 0) {
          delete items[key];
        }
      });

      return items;
    },
    errors: (state, getters) => (id) => {
      return getters.data('errors', id);
    },
    exists: (state) => (id) => {
      return state.models.hasOwnProperty(id);
    },
    hasErrors: (state, getters) => (id) => {
      return Object.keys(getters.errors(id)).length > 0;
    },
    hasChanges: (state, getters) => (id) => {
      return Object.keys(getters.changes(id)).length > 0;
    },
    hasFieldsInTab: (state) => (list, tab) => {
      let flag = false;

      // loop through all columns...
      Object.keys(tab.columns).forEach(c => {
        // ...and all their sections...
        Object.keys(tab.columns[c].sections).forEach(s => {
          // ...that have fields...
          if (tab.columns[c].sections[s].type === "fields") {
            // ...through all the field...
            Object.keys(tab.columns[c].sections[s].fields).forEach(f => {
              // ...and if a field is on the list,
              // the tab has one of the fields on itself
              if (list[f.toLowerCase()]) {
                flag = true;
              }
            });
          }
        });
      });

      return flag;
    },
    id: (state, getters, rootState) => (route) => {
      if (route.name === "Account") {
        return '/users/' + rootState.user.current.id;
      }

      return route.path === '/' ? '/site' : route.path;
    },
    model: (state, getters) => (id) => {
      return getters.exists(id) ? state.models[id] : {
        content: {},
        changes: {},
        errors: {}
      }
    },
    tabs: (state, getters) => (route, tabs) => {
      let id      = getters.id(route);
      let changes = getters.changes(id);
      let errors  = getters.errors(id);

      return tabs.map(tab => {
        let hasChanges = getters.hasFieldsInTab(changes, tab);
        let hasErrors  = getters.hasFieldsInTab(errors, tab);

        return {
          ...tab,
          theme: hasErrors ? 'errors' : (hasChanges ? 'changes' : '')
        };
      });
    },
    values: (state, getters) => (id) => {
      return clone({
        ...getters.model(id).content,
        ...getters.model(id).changes
      });
    },
  },
  mutations: {
    ADD_MODEL(state, [id, model]) {
      state.models = {
        ...state.models,
        [id]: model
      };
    },
    RESET_CHANGES(state, id) {
      Vue.set(state.models[id], "changes", {});
    },
    RESET_FIELD(state, [id, field]) {
      Vue.delete(state.models[id].changes, field);
    },
    SET_ERRORS(state, [id, errors]) {
      Vue.set(state.models[id], "errors", errors);
    },
    SET_CHANGES(state, [id, values]) {
      Vue.set(state.models[id], "changes", {
        ...state.models[id].changes,
        ...values
      });
    },
    SET_CONTENT(state, [id, values]) {
      Vue.set(state.models[id], "content", values);
    }
  },
  actions: {
    content(context, [id, values]) {
      context.dispatch("create", id);
      context.commit("SET_CONTENT", [id, values]);
    },
    create(context, id) {
      context.commit("ADD_MODEL", [id, context.getters.model(id)]);
    },
    errors(context, [id, errors]) {
      context.commit("SET_ERRORS", [id, errors]);
    },
    restore(context) {
      Object.keys(localStorage).map(id => {
        if (id.startsWith("kirby$")) {
          let values = localStorage.getItem(id);
          if (values) {
            id = id.replace("kirby$", "");
            context.dispatch("update", [id, JSON.parse(values)]);
          }
        }
      });
    },
    reset(context, id) {
      if (id) {
        context.commit("RESET_CHANGES", id);
        localStorage.removeItem("kirby$" + id);
        return;
      }

      Object.keys(context.state.models).forEach(id => {
        context.dispatch("reset", id);
      })
    },
    save(context, id) {
      // Prevent short blink of old value during API call
      // by already adding changes to store before API call
      let store = Object.assign({}, context.getters.values(id));
      context.dispatch("content", [id, store]);

      // Send to API
      return Api.patch(id.substr(1), context.getters.changes(id)).then(() => {
        context.dispatch("reset", id);
      });
    },
    update(context, [id, values]) {
      context.dispatch("create", id);

      values = Object.assign({}, values);

      // Remove unchanged values from changes store
      Object.keys(values).forEach(key => {
        // console.log('-------');
        // console.log(key);
        // console.log(context.getters.content(id).hasOwnProperty(key) &&
        // JSON.stringify(context.getters.content(id)[key]) === JSON.stringify(values[key]));
        // console.log(context.getters.content(id).hasOwnProperty(key));
        // console.log(JSON.stringify(context.getters.content(id)[key]) === JSON.stringify(values[key]));
        // console.log(JSON.stringify(context.getters.content(id)[key]));
        // console.log(JSON.stringify(values[key]));

        if (
          context.getters.content(id).hasOwnProperty(key) &&
          JSON.stringify(context.getters.content(id)[key]) === JSON.stringify(values[key])
        ) {
          Vue.delete(values, key);
          context.commit("RESET_FIELD", [id, key]);
        }
      });

      // console.log('@@@@@');
      // console.log(values);

      context.commit("SET_CHANGES", [id, values]);
      localStorage.setItem(
        "kirby$" + id,
        JSON.stringify(context.getters.changes(id))
      );
    }
  }
};
