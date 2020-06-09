import Vue from "vue";
import clone from "@/ui/helpers/clone.js";

export default {
  namespaced: true,

  state: {
    current: null,
    locking: {
      supported: false,
      lock: false,
      unlocked: false
    },
    models: {}
  },

  getters: {
    api: (state, getters, rootState) => (storeId) => {
      if (rootState.languages.current) {
        storeId = storeId.split("/");
        storeId = storeId.slice(0, storeId.length - 1).join("/");
      }

      return storeId;
    },
    badge: (state, getters) => (id, tab) => {
      let count = 0;

      tab.columns.forEach(column => {
        let changes  = Object.keys(getters["changes"](id));
        let sections = Object.values(column.sections);
            sections = sections.filter(section => section.type === "fields");

        sections.forEach(section => {
          let fields  = Object.keys(section.fields);
          let changed = fields.filter(field => changes.includes(field));
          count += changed.length;
        })
      })

      return count;
    },
    changes: (state, getters) => (id) => {
      return clone(getters.model(id).changes);
    },
    exists: (state) => (id) => {
      return state.models.hasOwnProperty(id);
    },
    hasChanges: (state, getters) => (id) => {
      const changes = getters.model(id).changes;
      return Object.keys(changes).length > 0;
    },
    id: (state, getters, rootState) => (id) => {
      id = id || state.current;

      if (rootState.languages.current) {
        return id + "/" + rootState.languages.current.code;
      }

      return id;
    },
    isCurrent: (state, getters) => (id) => {
      return id === state.current;
    },
    model: (state, getters) => (id) => {
      id = id || state.current;

      if (getters.exists(id) === true) {
        return state.models[id];
      }

      return {
        originals: {},
        values: {},
        changes: {},
      };
    },
    originals: (state, getters) => (id) => {
      return clone(getters.model(id).originals);
    },
    values: (state, getters) => (id) => {
      return {
        ...getters.originals(id),
        ...getters.changes(id)
      };
    }
  },

  mutations: {
    ACTIVATE_LOCKING(state) {
      Vue.set(state.locking, "supported", true);
    },
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

      localStorage.setItem(
        "kirby$content$" + id,
        JSON.stringify({
          originals: state.models[id].originals,
          changes:   state.models[id].changes
        })
      );
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
    RESET_LOCKING(state) {
      Vue.set(state, "locking", {
        supported: false,
        lock: false,
        unlocked: false
      });
    },
    REVERT_MODEL(state, id) {
      if (!state.models[id]) {
        return false;
      }

      Vue.set(state.models[id], "changes", {});
      localStorage.removeItem("kirby$content$" + id);
    },
    SET_CURRENT(state, id) {
      state.current = id;
    },
    SET_LOCK(state, lock) {
      Vue.set(state.locking, "lock", lock);
    },
    SET_UNLOCKED(state, unlocked) {
      // reset unsaved changes if content has been unlocked by another user
      if (unlocked === true) {
        unlocked = clone(state.models[state.current].changes);
        Vue.set(state.models[state.current], "changes", {});
      }

      Vue.set(state.locking, "unlocked", unlocked);
    }
  },

  actions: {
    create(context, { id, values }) {
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
      context.dispatch("hasLocking", false);
    },
    download(context) {
      const stored = context.state.locking.unlocked;
      let content  = "";

      Object.keys(stored).forEach(key => {
        content += key + ": \n\n" + stored[key];
        content += "\n\n----\n\n";
      });

      let link = document.createElement('a');
      link.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(content));
      link.setAttribute('download', context.getters["id"]() + ".txt");
      link.style.display = 'none';

      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    },
    hasLocking(context, hasLocking) {
      if (hasLocking === true) {
        return context.commit("ACTIVATE_LOCKING");
      }

      return context.commit("RESET_LOCKING");
    },
    input(context, { id, values }) {
      context.commit("INPUT_MODEL", {
        id:     id || context.state.current,
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
    logout(context) {
      // remove all form changes from localStorage
      Object.keys(localStorage).forEach(key => {
        if (key.startsWith("kirby$content$")) {
          localStorage.removeItem(key);
        }
      });
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
      context.commit("REVERT_MODEL", id || context.state.current);
    },
    unlocked(context, unlocked) {
      context.commit("SET_UNLOCKED", unlocked);
    },
    update(context, { id, values }) {
      id = id || context.state.current;

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
