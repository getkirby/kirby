import { isObject } from "@/helpers/object";
import { reactive } from "vue";
import throttle from "@/helpers/throttle.js";

/**
 * @since 5.0.0
 */
export default (panel) => {
	const content = reactive({
		/**
		 * Returns an object with all changed fields
		 * @param {Object} env
		 * @returns {Object}
		 */
		changes(env = {}) {
			// changes can only be computed for the current view
			if (this.isCurrent(env) === false) {
				throw new Error("Cannot get changes for another view");
			}

			const changes = {};

			for (const field in panel.view.props.content) {
				const changed = JSON.stringify(panel.view.props.content[field]);
				const original = JSON.stringify(panel.view.props.originals[field]);

				if (changed !== original) {
					changes[field] = panel.view.props.content[field];
				}
			}

			return changes;
		},

		dialog: null,

		/**
		 * Removes all unpublished changes
		 */
		async discard(env = {}) {
			if (this.isProcessing === true) {
				return;
			}

			// In the current view, we can use the existing
			// lock state to determine if we can discard
			if (this.isCurrent(env) === true && this.isLocked(env) === true) {
				throw new Error("Cannot discard locked changes");
			}

			this.isProcessing = true;

			try {
				await this.request("discard", {}, env);

				// update the props for the current view
				if (this.isCurrent(env)) {
					panel.view.props.content = panel.view.props.originals;
				}

				this.emit("discard", {}, env);
			} catch (error) {
				// handle locked states
				if (error.key.startsWith("error.content.lock")) {
					return this.lockDialog(error.details);
				}

				// let our regular error handler take over
				throw error;
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Emit a custom content event
		 * and add the api and language properties
		 */
		emit(event, options = {}, env = {}) {
			panel.events.emit("content." + event, {
				...options,
				...this.env(env)
			});
		},

		/**
		 * Ensure a consistent environment object
		 * with api and language properties
		 */
		env(env = {}) {
			return {
				api: panel.view.props.api,
				language: panel.language.code,
				...env
			};
		},

		/**
		 * Whether the api endpoint belongs to the current view
		 * @var {String} api
		 */
		isCurrent(env = {}) {
			const { api, language } = this.env(env);
			return panel.view.props.api === api && panel.language.code === language;
		},

		/**
		 * Whether the current view is locked
		 * @param {String} api
		 */
		isLocked(env = {}) {
			return this.lock(env).isLocked;
		},

		/**
		 * Whether content is currently being discarded, saved or published
		 * @var {Boolean}
		 */
		isProcessing: false,

		/**
		 * Get the lock state for the current view
		 * @param {String} api
		 */
		lock(env = {}) {
			if (this.isCurrent(env) === false) {
				throw new Error(
					"The lock state cannot be detected for content from another view"
				);
			}

			return panel.view.props.lock;
		},

		/**
		 * Opens the lock dialog to inform the current editor
		 * about edits from another user
		 */
		lockDialog(lock) {
			this.dialog = panel.dialog;
			this.dialog.open({
				component: "k-lock-alert-dialog",
				props: {
					lock: lock
				},
				on: {
					close: () => {
						this.dialog = null;
						panel.view.reload();
					}
				}
			});
		},

		/**
		 * Merge new content changes with the
		 * original values and update the view props
		 */
		merge(values = {}, env = {}) {
			if (this.isCurrent(env) === false) {
				throw new Error("The content in another view cannot be merged");
			}

			if (isObject(values) === false) {
				values = {};
			}

			panel.view.props.content = {
				...panel.view.props.originals,
				...values
			};

			return panel.view.props.content;
		},

		/**
		 * Publishes any changes
		 */
		async publish(values = {}, env = {}) {
			if (this.isProcessing === true) {
				return;
			}

			this.isProcessing = true;

			// Send updated values to API
			try {
				await this.request("publish", values, env);

				// close the dialog if it is still open
				this.dialog?.close();

				// update the props for the current view
				if (this.isCurrent(env) === true) {
					panel.view.props.originals = panel.view.props.content;
				}

				this.emit("publish", { values }, env);
			} catch (error) {
				// handle locked states
				if (error.key.startsWith("error.content.lock")) {
					return this.lockDialog(error.details);
				}

				throw error;
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Simplified request handler for all content API requests
		 */
		async request(method = "save", values = {}, env = {}) {
			const { api, language } = this.env(env);

			const options = {
				headers: {
					"x-language": language
				}
			};

			if (method === "save") {
				options.signal = this.saveAbortController.signal;
				options.silent = true;
			}

			return panel.api.post(api + "/changes/" + method, values, options);
		},

		/**
		 * Saves any changes
		 */
		async save(values = {}, env = {}) {
			this.isProcessing = true;

			// ensure to abort unfinished previous save request
			// to avoid race conditions with older content
			this.saveAbortController?.abort();
			this.saveAbortController = new AbortController();

			try {
				await this.request("save", values, env);

				this.isProcessing = false;

				// close the dialog if it is still open
				this.dialog?.close();

				// update the lock timestamp
				if (this.isCurrent(env) === true) {
					panel.view.props.lock.modified = new Date();
				}

				this.emit("save", { values }, env);
			} catch (error) {
				// handle aborted requests silently
				if (error.name === "AbortError") {
					return;
				}

				// processing must not be interrupted for aborted
				// requests because the follow-up request is already
				// in progress and setting the state to false here
				// would be wrong
				this.isProcessing = false;

				// handle locked states
				if (error.key.startsWith("error.content.lock")) {
					return this.lockDialog(error.details);
				}

				throw error;
			}
		},

		/**
		 * @internal
		 * @var {AbortController}
		 */
		saveAbortController: null,

		/**
		 * Updates the form values of the current view
		 */
		async update(values = {}, env = {}) {
			return await this.save(this.merge(values, env), env);
		},

		/**
		 * Updates the form values of the current view with a delay
		 */
		updateLazy(values = {}, env = {}) {
			this.saveLazy(this.merge(values, env), env);
		}
	});

	// create a delayed version of save
	// that we can use in the input event
	content.saveLazy = throttle(content.save, 1000, {
		leading: true,
		trailing: true
	});

	return content;
};
