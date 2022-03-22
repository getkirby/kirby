import Vue from "vue";
import { clone } from "@/helpers/object.js";

const keep = (id, data) => {
  localStorage.setItem("kirby$content$" + id, JSON.stringify(data));
};

export default {
  namespaced: true,

  state: {
    /**
     * ID of current model
     */
    current: null,

    /**
     * Object of models:
     *  Key   => type/slug/?language=languageCode, e.g. pages/blog+a-blog-post/?language=de
     *  Value => Object of
     *            - api: API endpoint
     *            - originals: values as they are in the content file
     *            - changes: values with unsaved changes
     */
    models: {},

    // whether form shall be disabled (e.g. for structure fields)
    status: {
      enabled: true
    }
  },

  getters: {
    // status getters

    /**
     * Checks for an ID if a model exists in the store
     */
    exists: (state) => (id) => {
      return Object.prototype.hasOwnProperty.call(state.models, id);
    },
    /**
     * Checks for an ID if a model has unsaved changes
     */
    hasChanges: (state, getters) => (id) => {
      const changes = getters.model(id).changes;
      return Object.keys(changes).length > 0;
    },
    /**
     * Checks for an ID if it is the current model
     */
    isCurrent: (state) => (id) => {
      return state.current === id;
    },

    // data getters

    /**
     * Returns ID (current or provided) with correct language suffix
     */
    id: (state) => (id) => {
      id = id || state.current;

      if (window.panel.$language) {
        return id + "?language=" + window.panel.$language.code;
      }

      return id;
    },
    /**
     * Return the full model object for passed ID
     */
    model: (state, getters) => (id) => {
      id = id || state.current;

      if (getters.exists(id) === true) {
        return state.models[id];
      }

      return {
        api: null,
        originals: {},
        values: {},
        changes: {}
      };
    },
    /**
     * Returns original (in content file) values for passed model ID
     */
    originals: (state, getters) => (id) => {
      return clone(getters.model(id).originals);
    },
    /**
     * Returns values (incl. unsaved changes) for passed model ID
     */
    values: (state, getters) => (id) => {
      return {
        ...getters.originals(id),
        ...getters.changes(id)
      };
    },
    /**
     * Returns unsaved changes for passed model ID
     */
    changes: (state, getters) => (id) => {
      return clone(getters.model(id).changes);
    }
  },

  mutations: {
    CLEAR(state) {
      Object.keys(state.models).forEach((key) => {
        state.models[key].changes = {};
      });

      // remove all form changes from localStorage
      Object.keys(localStorage).forEach((key) => {
        if (key.startsWith("kirby$content$")) {
          localStorage.removeItem(key);
        }
      });
    },
    CREATE(state, [id, model]) {
      if (!model) {
        return false;
      }

      // if model already in store, use stored changes,
      // otherwise fallback to provided changes
      let changes = state.models[id] ? state.models[id].changes : model.changes;

      Vue.set(state.models, id, {
        api: model.api,
        originals: model.originals,
        changes: changes || {}
      });
    },
    CURRENT(state, id) {
      state.current = id;
    },
    MOVE(state, [from, to]) {
      // move state
      const model = clone(state.models[from]);
      Vue.delete(state.models, from);
      Vue.set(state.models, to, model);

      // move local storage
      const storage = localStorage.getItem("kirby$content$" + from);
      localStorage.removeItem("kirby$content$" + from);
      localStorage.setItem("kirby$content$" + to, storage);
    },
    REMOVE(state, id) {
      Vue.delete(state.models, id);
      localStorage.removeItem("kirby$content$" + id);
    },
    REVERT(state, id) {
      if (state.models[id]) {
        Vue.set(state.models[id], "changes", {});
        localStorage.removeItem("kirby$content$" + id);
      }
    },
    STATUS(state, enabled) {
      Vue.set(state.status, "enabled", enabled);
    },
    UPDATE(state, [id, field, value]) {
      // avoid updating without a valid model
      if (!state.models[id]) {
        return false;
      }

      // avoid issues with undefined values
      if (value === undefined) {
        value = null;
      }

      value = clone(value);

      // // compare current field value with its original value
      const current = JSON.stringify(value);
      const original = JSON.stringify(state.models[id].originals[field]);

      if (original == current) {
        // if the same, there are no unsaved changes
        Vue.delete(state.models[id].changes, field);
      } else {
        // if they differ, set as unsaved change
        Vue.set(state.models[id].changes, field, value);
      }

      keep(id, {
        api: state.models[id].api,
        originals: state.models[id].originals,
        changes: state.models[id].changes
      });
    }
  },

  actions: {
    init(context) {
      // load models in store from localStorage
      Object.keys(localStorage)
        .filter((key) => key.startsWith("kirby$content$"))
        .map((key) => key.split("kirby$content$")[1])
        .forEach((id) => {
          const data = localStorage.getItem("kirby$content$" + id);
          context.commit("CREATE", [id, JSON.parse(data)]);
        });

      // load old format
      Object.keys(localStorage)
        .filter((key) => key.startsWith("kirby$form$"))
        .map((key) => key.split("kirby$form$")[1])
        .forEach((id) => {
          const json = localStorage.getItem("kirby$form$" + id);
          let data = null;

          try {
            data = JSON.parse(json);
          } catch (e) {
            // fail silently
          }

          if (!data || !data.api) {
            // remove invalid entry
            localStorage.removeItem("kirby$form$" + id);
            return false;
          }

          const model = {
            api: data.api,
            originals: data.originals,
            changes: data.values
          };

          // add it to the state
          context.commit("CREATE", [id, model]);

          // keep it in localStorage
          keep(id, model);

          // remove the old entry
          localStorage.removeItem("kirby$form$" + id);
        });
    },
    clear(context) {
      context.commit("CLEAR");
    },
    create(context, model) {
      const content = clone(model.content);

      // remove fields from the content object that
      // should be ignored in changes or when saving content
      if (Array.isArray(model.ignore)) {
        model.ignore.forEach((field) => delete content[field]);
      }

      // attach the language to the id
      model.id = context.getters.id(model.id);

      const data = {
        api: model.api,
        originals: content,
        changes: {}
      };

      context.commit("CREATE", [model.id, data]);
      context.dispatch("current", model.id);
    },
    current(context, id) {
      context.commit("CURRENT", id);
    },
    disable(context) {
      context.commit("STATUS", false);
    },
    enable(context) {
      context.commit("STATUS", true);
    },
    move(context, [from, to]) {
      from = context.getters.id(from);
      to = context.getters.id(to);
      context.commit("MOVE", [from, to]);
    },
    remove(context, id) {
      context.commit("REMOVE", id);

      if (context.getters.isCurrent(id)) {
        context.commit("CURRENT", null);
      }
    },
    revert(context, id) {
      id = id || context.state.current;
      context.commit("REVERT", id);
    },
    async save(context, id) {
      id = id || context.state.current;

      // don't allow save if model is not current
      // or the form is currently disabled
      if (
        context.getters.isCurrent(id) &&
        context.state.status.enabled === false
      ) {
        return false;
      }

      // disable form while saving
      context.dispatch("disable");

      const model = context.getters.model(id);
      const data = { ...model.originals, ...model.changes };

      // Send updated values to API
      try {
        await Vue.$api.patch(model.api, data);

        // re-create model with updated values as originals
        context.commit("CREATE", [
          id,
          {
            ...model,
            originals: data
          }
        ]);

        // revert unsaved changes (which also removes localStorage entry)
        context.dispatch("revert", id);
      } finally {
        context.dispatch("enable");
      }
    },
    update(context, [field, value, id]) {
      id = id || context.state.current;
      context.commit("UPDATE", [id, field, value]);
    }
  }
};
