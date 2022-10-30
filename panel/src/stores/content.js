import { set, del } from "vue";
import { clone } from "@/helpers/object.js";

const keep = (id, data) => {
	localStorage.setItem("kirby$content$" + id, JSON.stringify(data));
};

export default {
	/**
	 * State
	 */
	state: {
		// ID of current model
		current: null,

		// Object of models:
		//   Key   => type/slug/?language=languageCode,
		// 					e.g. pages/blog+a-blog-post/?language=de
		//   Value => Object of
		//             - api: API endpoint
		//             - originals: values as they are in the content file
		//             - changes: values with unsaved changes
		models: {},

		// whether form shall be disabled (e.g. for structure fields)
		status: {
			enabled: true
		}
	},

	/**
	 * Getters
	 */
	exists(id) {
		// Checks for an ID if a model exists in the store
		return Object.prototype.hasOwnProperty.call(this.state.models, id);
	},
	hasChanges(id) {
		// Checks for an ID if a model has unsaved changes
		const changes = this.model(id).changes;
		return Object.keys(changes).length > 0;
	},
	isCurrent(id) {
		// Checks for an ID if it is the current model
		return this.state.current === id;
	},
	id(id) {
		// Returns ID (current or provided) with correct language suffix
		id = id || this.state.current;

		if (window.panel.$language) {
			return id + "?language=" + window.panel.$language.code;
		}

		return id;
	},
	model(id) {
		// Return the full model object for passed ID
		id = id || this.state.current;

		if (this.exists(id) === true) {
			return this.state.models[id];
		}

		return {
			api: null,
			originals: {},
			values: {},
			changes: {}
		};
	},
	originals(id) {
		// Returns original (in content file) values for passed model ID
		return clone(this.model(id).originals);
	},
	values(id) {
		// Returns values (incl. unsaved changes) for passed model ID
		return {
			...this.originals(id),
			...this.changes(id)
		};
	},
	changes(id) {
		// Returns unsaved changes for passed model ID
		return clone(this.model(id).changes);
	},

	/**
	 * Actions
	 */
	clear() {
		for (const key in this.state.models) {
			this.state.models[key].changes = {};
		}

		// remove all form changes from localStorage
		for (const key in localStorage) {
			if (key.startsWith("kirby$content$")) {
				localStorage.removeItem(key);
			}
		}
	},
	addModel([id, data]) {
		if (!data) {
			return false;
		}

		// if model already in store, use stored changes,
		// otherwise fallback to provided changes
		let changes = this.state.models[id]
			? this.state.models[id].changes
			: data.changes;

		set(this.state.models, id, {
			api: data.api,
			originals: data.originals,
			changes: changes || {}
		});
	},
	setCurrent(id) {
		this.state.current = id;
	},
	move([from, to]) {
		from = this.id(from);
		to = this.id(to);

		// move state
		const model = clone(this.state.models[from]);
		del(this.state.models, from);
		set(this.state.models, to, model);

		// move local storage
		const storage = localStorage.getItem("kirby$content$" + from);
		localStorage.removeItem("kirby$content$" + from);
		localStorage.setItem("kirby$content$" + to, storage);
	},
	removeModel(id) {
		del(this.state.models, id);
		localStorage.removeItem("kirby$content$" + id);
	},
	revert(id) {
		id = id || this.state.current;

		if (this.state.models[id]) {
			set(this.state.models[id], "changes", {});
			localStorage.removeItem("kirby$content$" + id);
		}
	},
	setStatus(enabled) {
		set(this.state.status, "enabled", enabled);
	},
	updateModel([id, field, value]) {
		// avoid updating without a valid model
		if (!this.state.models[id]) {
			return false;
		}

		// avoid issues with undefined values
		if (value === undefined) {
			value = null;
		}

		value = clone(value);

		// // compare current field value with its original value
		const current = JSON.stringify(value);
		const original = JSON.stringify(this.state.models[id].originals[field]);

		if (original == current) {
			// if the same, there are no unsaved changes
			del(this.state.models[id].changes, field);
		} else {
			// if they differ, set as unsaved change
			set(this.state.models[id].changes, field, value);
		}

		keep(id, {
			api: this.state.models[id].api,
			originals: this.state.models[id].originals,
			changes: this.state.models[id].changes
		});
	},

	// Actions

	init() {
		// load models in store from localStorage
		Object.keys(localStorage)
			.filter((key) => key.startsWith("kirby$content$"))
			.map((key) => key.split("kirby$content$")[1])
			.forEach((id) => {
				const data = localStorage.getItem("kirby$content$" + id);
				this.create([id, JSON.parse(data)]);
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
				this.create([id, model]);

				// keep it in localStorage
				keep(id, model);

				// remove the old entry
				localStorage.removeItem("kirby$form$" + id);
			});
	},
	create(model) {
		const content = clone(model.content);

		// remove fields from the content object that
		// should be ignored in changes or when saving content
		if (Array.isArray(model.ignore)) {
			model.ignore.forEach((field) => delete content[field]);
		}

		// attach the language to the id
		model.id = this.id(model.id);

		const data = {
			api: model.api,
			originals: content,
			changes: {}
		};

		this.addModel([model.id, data]);
		this.setCurrent(model.id);
	},
	disable() {
		this.setStatus(false);
	},
	enable() {
		this.setStatus(true);
	},
	remove(id) {
		this.removeModel(id);

		if (this.isCurrent(id)) {
			this.setCurrent(null);
		}
	},
	async save(id) {
		id = id || this.state.current;

		// don't allow save if model is not current
		// or the form is currently disabled
		if (this.isCurrent(id) && this.state.status.enabled === false) {
			return false;
		}

		// disable form while saving
		this.disable();

		const model = this.model(id);
		const data = { ...model.originals, ...model.changes };

		// Send updated values to API
		try {
			await window.panel.$api.patch(model.api, data);

			// re-create model with updated values as originals
			this.addModel([
				id,
				{
					...model,
					originals: data
				}
			]);

			// revert unsaved changes (which also removes localStorage entry)
			this.revert(id);
		} finally {
			this.enable();
		}
	},
	update([field, value, id]) {
		id = id || this.state.current;

		if (field === null) {
			for (const field in value) {
				this.updateModel([id, field, value[field]]);
			}
		} else {
			this.updateModel([id, field, value]);
		}
	}
};
