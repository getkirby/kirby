import { set, del } from "vue";
import { length } from "@/helpers/object.js";

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
		exists: (state) => (id) => Object.hasOwn(state.models, id),
		/**
		 * Checks for an ID if a model has unsaved changes
		 */
		hasChanges: (state, getters) => (id) => {
			const changes = getters.model(id).changes;
			return length(changes) > 0;
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
			id = id ?? state.current;

			if (id && id.includes("?language=") === false) {
				id += "?language=" + window.panel.language.code;
			}

			return id;
		},
		/**
		 * Return the full model object for passed ID
		 */
		model: (state, getters) => (id) => {
			id = getters.id(id);

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
			return structuredClone(getters.model(id).originals);
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
			return structuredClone(getters.model(id).changes);
		}
	},

	mutations: {
		CLEAR(state) {
			for (const key in state.models) {
				state.models[key].changes = {};
			}

			// remove all form changes from localStorage
			for (const key in localStorage) {
				if (key.startsWith("kirby$content$")) {
					localStorage.removeItem(key);
				}
			}
		},
		CREATE(state, [id, model]) {
			if (!model) {
				return false;
			}

			// if model already in store, use stored changes,
			// otherwise fallback to provided changes
			let changes = state.models[id] ? state.models[id].changes : model.changes;

			set(state.models, id, {
				api: model.api,
				originals: model.originals,
				changes: changes ?? {}
			});
		},
		CURRENT(state, id) {
			state.current = id;
		},
		MOVE(state, [from, to]) {
			// move state
			const model = structuredClone(state.models[from]);
			del(state.models, from);
			set(state.models, to, model);

			// move local storage
			const storage = localStorage.getItem("kirby$content$" + from);
			localStorage.removeItem("kirby$content$" + from);
			localStorage.setItem("kirby$content$" + to, storage);
		},
		REMOVE(state, id) {
			del(state.models, id);
			localStorage.removeItem("kirby$content$" + id);
		},
		REVERT(state, id) {
			if (state.models[id]) {
				set(state.models[id], "changes", {});
				localStorage.removeItem("kirby$content$" + id);
			}
		},
		STATUS(state, enabled) {
			set(state.status, "enabled", enabled);
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

			value = structuredClone(value);

			// // compare current field value with its original value
			const current = JSON.stringify(value);
			const original = JSON.stringify(
				state.models[id].originals[field] ?? null
			);

			if (original == current) {
				// if the same, there are no unsaved changes
				del(state.models[id].changes, field);
			} else {
				// if they differ, set as unsaved change
				set(state.models[id].changes, field, value);
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
			for (const key in localStorage) {
				// load models in store from localStorage
				if (key.startsWith("kirby$content$")) {
					const id = key.split("kirby$content$")[1];
					const data = localStorage.getItem("kirby$content$" + id);
					context.commit("CREATE", [id, JSON.parse(data)]);
					continue;
				}

				// load old format
				if (key.startsWith("kirby$form$")) {
					const id = key.split("kirby$form$")[1];
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
				}
			}
		},
		clear(context) {
			context.commit("CLEAR");
		},
		create(context, model) {
			const content = structuredClone(model.content);

			// remove fields from the content object that
			// should be ignored in changes or when saving content
			if (Array.isArray(model.ignore)) {
				for (const field of model.ignore) {
					delete content[field];
				}
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
			id = context.getters.id(id);
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
			id = context.getters.id(id);
			context.commit("REMOVE", id);

			if (context.getters.isCurrent(id)) {
				context.commit("CURRENT", null);
			}
		},
		revert(context, id) {
			id = context.getters.id(id);
			context.commit("REVERT", id);
		},
		async save(context, id) {
			id = context.getters.id(id);

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
				await window.panel.api.patch(model.api, data);

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
			id = id ?? context.state.current;

			if (field === null) {
				for (const field in value) {
					context.commit("UPDATE", [id, field, value[field]]);
				}
			} else {
				context.commit("UPDATE", [id, field, value]);
			}
		}
	}
};
