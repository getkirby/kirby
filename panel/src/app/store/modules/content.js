import Vue from "vue";
import clone from "@/ui/helpers/clone.js";

const keep = (id, data) => {
  localStorage.setItem(
    "kirby$content$" + id,
    JSON.stringify(data)
  );
};

export default {
  namespaced: true,

  state: {
    current: {
      id: null,
      lock: false,
      unlocked: false
    },
    models: {}
  },

  getters: {
    exists: state => id => {
      return state.models.hasOwnProperty(id);
    },
    hasChanges: (state, getters) => id => {
      const changes = getters.model(id).changes;
      return Object.keys(changes).length > 0;
    },
    id: (state, getters, rootState) => id => {
      id = id || state.current.id;

      if (rootState.languages.current) {
        return id + "/" + rootState.languages.current.code;
      }

      return id;
    },
    isCurrent: (state, getters) => id => {
      return id === state.current.id;
    },
    model: (state, getters) => id => {
      id = id || state.current.id;

      if (getters.exists(id) === true) {
        return state.models[id];
      }

      return {
        originals: {},
        values: {},
        changes: {},
      };
    },
    values: (state, getters) => id => {
      return {
        ...getters.originals(id),
        ...getters.changes(id)
      };
    },
    originals: (state, getters) => id => {
      return clone(getters.model(id).originals);
    },
    changes: (state, getters) => id => {
      return clone(getters.model(id).changes);
    }
  },

  mutations: {
    ADD_MODEL(state, { id, model }) {
      if (!model) {
        return false;
      }

      // if model already exists in store, keep stored changes
      let changes = state.models.hasOwnProperty(id)
                  ? state.models[id].changes
                  : model.changes ;

      Vue.set(state.models, id, {
        originals: model.originals,
        changes:   changes || {}
      });
    },
    INPUT_MODEL(state, { id, values }) {
      if (!state.models[id]) {
        return false;
      }

      Object.keys(values).forEach(field => {
        const value = clone(values[field]);

        // compare current field value with its original value
        const current  = JSON.stringify(value);
        const original = JSON.stringify(state.models[id].originals[field]);

        // if same, there are no unsaved changes
        if (original === current) {
          Vue.delete(state.models[id].changes, field);

        // if different, set as unsaved change
        } else {
          Vue.set(state.models[id].changes, field, value);
        }
      });

      keep(id, {
        originals: state.models[id].originals,
        changes:   state.models[id].changes
      });
    },
    MOVE_MODEL(state, { from, to }) {
      // move state
      const model = clone(state.models[from]);
      Vue.delete(state.models, from);
      Vue.set(state.models, to, model);

      // move local storage
      const storage = localStorage.getItem("kirby$content$" + from);
      localStorage.removeItem("kirby$content$" + from);
      localStorage.setItem("kirby$content$" + to, storage);
    },
    REMOVE_MODEL(state, id) {
      Vue.delete(state.models, id);
      localStorage.removeItem("kirby$content$" + id);
    },
    REVERT_MODEL(state, id) {
      if (!state.models[id]) {
        return false;
      }

      Vue.set(state.models[id], "changes", {});
      localStorage.removeItem("kirby$content$" + id);
    },
    SET_CURRENT(state, id) {
      state.current.id = id;
    },
    SET_LOCK(state, lock) {
      Vue.set(state.current, "lock", lock);
    },
    SET_UNLOCKED(state, unlocked) {
      // reset unsaved changes if content has been unlocked by another user
      if (unlocked) {
        Vue.set(state.models[state.current.id], "changes", {});
      }

      Vue.set(state.current, "unlocked", unlocked);
    }
  },

  actions: {
    create(context, { id, values }) {
      // attach the language to the id
      id = context.getters.id(id);

      // remove title from model content
      if (id.startsWith("pages/") || id.startsWith("site")) {
        delete values.title;
      }

      context.commit("ADD_MODEL", {
        id: id,
        model: {
          originals: clone(values),
          changes:   {}
        }
      });
      context.dispatch("current", id);
    },
    current(context, id) {
      context.commit("SET_CURRENT", id);
    },
    input(context, { id, values }) {
      context.commit("INPUT_MODEL", {
        id:     id || context.state.current.id,
        values: values
      });
    },
    load(context) {
      const stored = Object.keys(localStorage).filter(key => {
        return key.startsWith("kirby$content$");
      });
      const ids    = stored.map(key => key.split("kirby$content$")[1]);

      ids.forEach(id => {
        const data = localStorage.getItem("kirby$content$" + id);
        context.commit("ADD_MODEL", { id: id, model: JSON.parse(data) });
      });
    },
    lock(context, lock) {
      context.commit("SET_LOCK", lock);
    },
    move(context, { from, to }) {
      context.commit("MOVE_MODEL", {
        from: context.getters.id(from),
        to:   context.getters.id(to)
      });
    },
    remove(context, id) {
      context.commit("REMOVE_MODEL", id);

      if (context.getters.isCurrent(id)) {
        context.commit("SET_CURRENT", null);
      }
    },
    revert(context, id) {
      context.commit("REVERT_MODEL", id || context.state.current.id);
    },
    unlocked(context, unlocked) {
      context.commit("SET_UNLOCKED", unlocked);
    },
    update(context, { id, values }) {
      id = id || context.state.current.id;

      // re-create model with updated values as originals
      context.commit("ADD_MODEL", {
        id: id,
        model: {
          ...context.getters.model(id),
          originals: values
        }
      });

      // revert unsaved changes (which also removes localStorage entry)
      context.dispatch("revert", id);
    }
  }
};
