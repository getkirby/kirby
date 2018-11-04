import Vue from "vue";
import Api from "@/api/api.js";
import clone from "@/ui/helpers/clone.js";

export default {
  namespaced: true,
  state: {
    models: {}
  },
  getters: {
    exists: (state) => (id) => {
      return state.models.hasOwnProperty(id);
    },
    hasChanges: (state, getters) => (id) => {
      return Object.keys(getters.model(id).changes).length > 0;
    },
    id: (state, getters, rootState) => (route) => {
      if (route.name === "Account") {
        return '/users/' + rootState.user.current.id;
      }

      return route.path === '/' ? '/site' : route.path;
    },
    model: (state, getters) => (id) => {
      return getters.exists(id) ? state.models[id] : {
        originals: {},
        values: {},
        changes: {},
      };
    },
    originals: (state, getters) => (id) => {
      return clone(getters.model(id).originals);
    },
    values: (state, getters) => (id) => {
      return clone(getters.model(id).values);
    },
  },
  mutations: {
    CREATE(state, model) {
      Vue.set(state.models, model.id, {
        originals: clone(model.content),
        values: clone(model.content),
        changes: {}
      });
    },
    DELETE_CHANGES(state, id) {
      Vue.set(state.models[id], "changes", {});
      localStorage.removeItem("kirby$" + id);
    },
    SET_ORIGINALS(state, [id, originals]) {
      state.models[id].originals = clone(originals);
    },
    SET_VALUES(state, [id, values]) {
      state.models[id].values = clone(values);
    },
    UPDATE(state, [id, field, value]) {

      Vue.set(state.models[id].values, field, value);

      const original = JSON.stringify(state.models[id].originals[field]);
      const current  = JSON.stringify(value);

      if (original === current) {
        Vue.delete(state.models[id].changes, field);
      } else {
        Vue.set(state.models[id].changes, field, true);
      }

      localStorage.setItem(
        "kirby$" + id,
        JSON.stringify(state.models[id].values)
      );

    }
  },
  actions: {
    create(context, model) {
      context.commit("CREATE", model);

      const values = localStorage.getItem("kirby$" + model.id);

      if (values) {
        const data = JSON.parse(values);

        Object.keys(data).forEach(field => {
          const value = data[field];
          context.commit("UPDATE", [model.id, field, value]);
        });

      }

    },
    revert(context, id) {
      // fetch from api
      return Api.get(id.substr(1), { select: "content" }).then(response => {
        context.commit("SET_ORIGINALS", [id, response.content]);
        context.commit("SET_VALUES", [id, response.content]);
        context.commit("DELETE_CHANGES", id);
      });
    },
    save(context, id) {
      // Send to api
      return Api.patch(id.substr(1), context.getters.values(id)).then(() => {
        context.dispatch("revert", id);
      });
    },
    update(context, [id, field, value]) {
      context.commit("UPDATE", [id, field, value]);
    }
  }
};
