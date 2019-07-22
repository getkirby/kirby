import Vue from "vue";
import Api from "@/api/api.js";
import clone from "@/helpers/clone.js";

export default {
  namespaced: true,
  state: {
    models: {},
    current: null,
    isDisabled: false,
    lock: null,
    unlock: null
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
      return state.current === id;
    },
    isDisabled: (state) => {
      return state.isDisabled === true;
    },
    lock: state => {
      return state.lock;
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
      id = id || state.current;
      return clone(getters.model(id).values);
    },
    unlock: state => {
      return state.unlock;
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
    DELETE_CHANGES(state, id) {
      Vue.set(state.models[id], "changes", {});
      Vue.set(state.models[id], "values", clone(state.models[id].originals));
      localStorage.removeItem("kirby$form$" + id);
    },
    IS_DISABLED(state, disabled) {
      state.isDisabled = disabled;
    },
    LOCK(state, lock) {
      state.lock = lock;
    },
    MOVE(state, ids) {
      // move state
      const model = clone(state.models[ids.old]);
      Vue.delete(state.models, ids.old);
      Vue.set(state.models, ids.new, model);

      // move local storage
      const storage = localStorage.getItem("kirby$form$" + ids.old);
      localStorage.removeItem("kirby$form$" + ids.old);
      localStorage.setItem("kirby$form$" + ids.new, storage);
    },
    REMOVE(state, id) {
      Vue.delete(state.models, id);
      localStorage.removeItem("kirby$form$" + id);
    },
    SET_ORIGINALS(state, [id, originals]) {
      state.models[id].originals = clone(originals);
    },
    SET_VALUES(state, [id, values]) {
      state.models[id].values = clone(values);
    },
    UNLOCK(state, unlock) {
      state.unlock = unlock;
    },
    UPDATE(state, [id, field, value]) {

      // avoid updating without a valid model
      if (!state.models[id]) {
        return false;
      }

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
        JSON.stringify({
          api: state.models[id].api,
          originals: state.models[id].originals,
          values: state.models[id].values,
          changes: state.models[id].changes
        })
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

      if (model.id.startsWith("pages/") || model.id.startsWith("site")) {
        // remove title from model content
        delete model.content.title;
      }

      context.commit("CREATE", model);
      context.commit("CURRENT", model.id);
      context.dispatch("load", model);
    },
    load(context, model) {
      const stored = localStorage.getItem("kirby$form$" + model.id);

      if (stored) {
        const data = JSON.parse(stored);

        Api.get(model.api + "/unlock").then(response => {
          if (
            response.supported === false ||
            response.unlocked === false
          ) {
            Object.keys(data.values).forEach(field => {
              const value = data.values[field];
              context.commit("UPDATE", [model.id, field, value]);
            });
            return;
          }

          context.commit("UNLOCK", data.values);
        });
      }
    },
    disable(context) {
      context.commit("IS_DISABLED", true);
    },
    enable(context) {
      context.commit("IS_DISABLED", false);
    },
    lock(context, lock) {
      context.commit("LOCK", lock);
    },
    move(context, ids) {
      context.commit("MOVE", ids);
    },
    remove(context, id) {
      context.commit("REMOVE", id);
    },
    reset(context) {
      context.commit("CURRENT", null);
      context.commit("LOCK", null);
      context.commit("UNLOCK", null);
    },
    revert(context, id) {
      const model = context.getters.model(id);

      // fetch from api
      return Api.get(model.api, { select: "content" }).then(response => {

        if (id.startsWith("pages/") || id.startsWith("site")) {
          // remove title from response content
          delete response.content.title;
        }

        context.commit("SET_ORIGINALS", [id, response.content]);
        context.commit("SET_VALUES", [id, response.content]);
        context.commit("DELETE_CHANGES", id);
      });
    },
    save(context, id) {

      id = id || context.state.current;

      const model = context.getters.model(id);

      if (context.getters.isCurrent(id)) {
        if (context.state.isDisabled) {
          return false;
        }
      }

      context.dispatch("disable");

      // Send to api
      return Api
        .patch(model.api, model.values)
        .then(() => {
          context.dispatch("revert", id);
          context.dispatch("enable");
        })
        .catch(error => {
          context.dispatch("enable");
          throw error;
        });
    },
    unlock(context, unlock) {
      context.commit("UNLOCK", unlock);
    },
    update(context, [id, field, value]) {
      context.commit("UPDATE", [id, field, value]);
    }
  }
};
