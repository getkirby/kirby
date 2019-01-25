import Vue from "vue";
import Api from "@/api/api.js";
import clone from "@/helpers/clone.js";

export default {
  namespaced: true,
  state: {
    models: {},
    current: null,
    isLocked: false
  },
  getters: {
    current: state => {
      return state.current;
    },
    exists: state => id => {
      return state.models.hasOwnProperty(id);
    },
    hasChanges: (state, getters) => id => {
      return Object.keys(getters.model(id).changes).length > 0;
    },
    id: (state, getters, rootState) => id => {
      if (rootState.languages.current) {
        return id + "/" + rootState.languages.current.code;
      } else {
        return id;
      }
    },
    isCurrent: (state) => id => {
      return state.current = id;
    },
    model: (state, getters) => id => {
      return getters.exists(id)
        ? state.models[id]
        : {
            originals: {},
            values: {},
            changes: {},
            api: null
          };
    },
    originals: (state, getters) => id => {
      return clone(getters.model(id).originals);
    },
    values: (state, getters) => id => {
      return clone(getters.model(id).values);
    }
  },
  mutations: {
    CREATE(state, model) {
      Vue.set(state.models, model.id, {
        api: model.api,
        originals: clone(model.content),
        values: clone(model.content),
        changes: {}
      });
    },
    CURRENT(state, id) {
      state.current = id;
    },
    IS_LOCKED(state, locked) {
      state.isLocked = locked;
    },
    REMOVE(state, id) {
      Vue.delete(state.models, id);
      localStorage.removeItem("kirby$form$" + id);
    },
    DELETE_CHANGES(state, id) {
      Vue.set(state.models[id], "changes", {});
      localStorage.removeItem("kirby$form$" + id);
    },
    SET_ORIGINALS(state, [id, originals]) {
      state.models[id].originals = clone(originals);
    },
    SET_VALUES(state, [id, values]) {
      state.models[id].values = clone(values);
    },
    UPDATE(state, [id, field, value]) {
      value = clone(value);

      Vue.set(state.models[id].values, field, value);

      const original = JSON.stringify(state.models[id].originals[field]);
      const current = JSON.stringify(value);

      if (original === current) {
        Vue.delete(state.models[id].changes, field);
      } else {
        Vue.set(state.models[id].changes, field, true);
      }

      localStorage.setItem(
        "kirby$form$" + id,
        JSON.stringify(state.models[id].values)
      );
    }
  },
  actions: {
    create(context, model) {
      // attach the language to the id
      if (
        context.rootState.languages.current &&
        context.rootState.languages.current.code
      ) {
        model.id = context.getters.id(model.id);
      }

      context.commit("CREATE", model);
      context.commit("CURRENT", model.id);

      const values = localStorage.getItem("kirby$form$" + model.id);

      if (values) {
        const data = JSON.parse(values);

        Object.keys(data).forEach(field => {
          const value = data[field];
          context.commit("UPDATE", [model.id, field, value]);
        });
      }
    },
    remove(context, id) {
      context.commit("REMOVE", id);
    },
    revert(context, id) {
      const model = context.getters.model(id);

      // fetch from api
      return Api.get(model.api, { select: "content" }).then(response => {
        context.commit("SET_ORIGINALS", [id, response.content]);
        context.commit("SET_VALUES", [id, response.content]);
        context.commit("DELETE_CHANGES", id);
      });
    },
    save(context, id) {

      id = id || context.state.current;

      const model = context.getters.model(id);

      if (context.getters.isCurrent(id)) {
        if (context.state.isLocked) {
          return false;
        }
      }

      // Send to api
      return Api.patch(model.api, model.values).then(() => {
        context.dispatch("revert", id);
      });
    },
    lock(context) {
      context.commit("IS_LOCKED", true);
    },
    unlock(context) {
      context.commit("IS_LOCKED", false);
    },
    update(context, [id, field, value]) {
      context.commit("UPDATE", [id, field, value]);
    }
  }
};
