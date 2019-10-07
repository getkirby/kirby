import Vue from "vue";
import Api from "@/api/api.js";
import clone from "@/helpers/clone.js";

export default {
  namespaced: true,
  
  state: {
    /**
     * ID of current model
     */
    current: null,
    
    /**
     * Object of models:
     *  Key   => type/slug/language, e.g. pages/blog+a-blog-post/de
     *  Value => Object of
     *            - api: API endpoint
     *            - originals: values as they are in the content file
     *            - changes: values with unsaved changes
     */
    models: {},
    
    /**
     * Object of status flags/info
     */
    status: {
      // whether form shall be disabled (e.g. for structure fields)
      enabled: true,
      
      // content lock info
      lock: null,
      
      // content unlock info
      unlock: null
    }
  },
  
  
  getters: {
    // status getters
    
    /**
     * Checks for an ID if a model exists in the store
     */
    exists: state => id => {
      return state.models.hasOwnProperty(id);
    },
    /**
     * Checks for an ID if a model has unsaved changes
     */
    hasChanges: (state, getters) => id => {
      const changes = getters.model(id).changes;
      return Object.keys(changes).length > 0;
    },
    /**
     * Checks for an ID if it is the current model
     */
    isCurrent: (state) => id => {
      return state.current === id;
    },

    // data getters
    
    /**
     * Returns ID (current or provided) with correct language suffix
     */
    id: (state, getters, rootState) => id => {
      id = id || state.current;
      
      if (rootState.languages.current) {
        return id + "/" + rootState.languages.current.code;
      } else {
        return id;
      }
    },
    /**
     * Return the full model object for passed ID
     */
    model: (state, getters) => id => {
      id = id || state.current;

      if (getters.exists(id) === true) {
        return state.models[id];
      }

      return {
        api: null,
        originals: {},
        values: {},
        changes: {},
      };
    },
    /**
     * Returns original (in content file) values for passed model ID
     */
    originals: (state, getters) => id => {
      return clone(getters.model(id).originals);
    },
    /**
     * Returns values (incl. unsaved changes) for passed model ID
     */
    values: (state, getters) => id => {
      return {
        ...getters.originals(id),
        ...getters.changes(id)
      };
    },
    /**
     * Returns unsaved changes for passed model ID
     */
    changes: (state, getters) => id => {
      return clone(getters.model(id).changes);
    }
  },
  
  
  mutations: {
    CREATE(state, [id, model]) {
      // if model already in store, use stored changes, 
      // otherwise fallback to provided changes
      let changes = state.models[id] ? state.models[id].changes : model.changes ;

      Vue.set(state.models, id, {
        api: model.api,
        originals: model.originals,
        changes: changes || {}
      });
    },
    CURRENT(state, id) {
      state.current = id;
    },
    LOCK(state, lock) {
      Vue.set(state.status, "lock", lock);
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
      Vue.set(state.models[id], "changes", {});
      localStorage.removeItem("kirby$content$" + id);
    },
    STATUS(state, enabled) {
      Vue.set(state.status, "status", enabled);
    },
    UNLOCK(state, unlock) {
      if (unlock) {
        // reset unsaved changes if content has been unlocked by another user
        Vue.set(state.models[state.current], "changes", {});
      }

      Vue.set(state.status, "unlock", unlock);
    },
    UPDATE(state, [id, field, value]) {
      // avoid updating without a valid model
      if (!state.models[id]) {
        return false;
      }

      value = clone(value);
      
      // compare current field value with its original value
      const current  = JSON.stringify(value);
      const original = JSON.stringify(state.models[id].originals[field]);

      if (original === current) {
        // if the same, there are no unsaved changes
        Vue.delete(state.models[id].changes, field);
      } else {
        // if they differ, set as unsaved change
        Vue.set(state.models[id].changes, field, value);
      }

      localStorage.setItem(
        "kirby$content$" + id,
        JSON.stringify({
          api: state.models[id].api,
          originals: state.models[id].originals,
          changes: state.models[id].changes
        })
      );
    }
  },
  
  
  actions: {
    init(context) {
      // load models in store from localStorage
      Object.keys(localStorage)
            .filter(key => key.startsWith("kirby$content$"))
            .map(key => key.split("kirby$content$")[1])
            .forEach(id => {
              const data = localStorage.getItem("kirby$content$" + id);
              context.commit("CREATE", [id, JSON.parse(data)]);
            });
    },
    create(context, model) {
      // attach the language to the id
      model.id = context.getters.id(model.id);

      // remove title from model content
      if (model.id.startsWith("pages/") || model.id.startsWith("site")) {
        delete model.content.title;
      }

      const data = {
        api: model.api,
        originals: clone(model.content),
        changes: {}
      };

      // check if content was previously unlocked
      Api.get(model.api + "/unlock").then(response => {
        if (
          response.supported === true &&
          response.unlocked === true
        ) {
          context.commit("UNLOCK", context.state.models[model.id].changes);
        }
      });

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
    lock(context, lock) {
      context.commit("LOCK", lock);
    },
    move(context, [from, to]) {
      from = context.getters.id(from);
      to   = context.getters.id(to);
      context.commit("MOVE", [from, to]);
    },
    remove(context, id) {
      context.commit("REMOVE", id);
    },
    revert(context, id) {
      id = id || context.state.current;
      context.commit("REVERT", id);
    },
    save(context, id) {
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
      const data  = {...model.originals, ...model.changes};

      // Send updated values to API
      return Api
        .patch(model.api, data)
        .then(() => {
          // re-create model with updated values as originals
          context.commit("CREATE", [id, { originals: data }]);
          // revert unsaved changes (which also removes localStorage enty)
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
    update(context, [field, value, id]) {
      id = id || context.state.current;
      context.commit("UPDATE", [id, field, value]);
    }
  }
};
