import { isObject, length } from "@/helpers/object";
import { reactive } from "vue";
import lane from "@/helpers/lane";
import throttle from "@/helpers/throttle";

/**
 * @since 5.0.0
 */
export default (panel) => {
	// all content requests run one after another
	const requests = lane();

	const content = reactive({
		/**
		 * Cancels any scheduled save request. A running request
		 * cannot be cancelled: the server would finish it regardless.
		 */
		cancelSaving() {
			this.saveLazy.cancel();
		},

		dialog: null,

		/**
		 * Returns an object with all changed fields
		 * @param {Object} env
		 * @returns {Object}
		 */
		diff(env = {}) {
			// changes can only be computed for the current view
			if (this.isCurrent(env) === false) {
				throw new Error("Cannot get changes for another view");
			}

			const versions = this.versions();
			const diff = {};

			for (const field in versions.changes) {
				const changed = JSON.stringify(versions.changes[field]);
				const original = JSON.stringify(versions.latest[field]);

				if (changed !== original) {
					diff[field] = versions.changes[field];
				}
			}

			// find all fields that have been present in the original content
			// but have been removed from the current content
			for (const field in versions.latest) {
				if (versions.changes[field] === undefined) {
					diff[field] = null;
				}
			}

			return diff;
		},

		/**
		 * Removes all unpublished changes
		 */
		async discard(env = {}) {
			if (this.isProcessing === true) {
				return;
			}

			// Only discard changes from the current view
			if (this.isCurrent(env) === false) {
				throw new Error("Cannot discard content from another view");
			}

			// Check the lock state to determine if we can discard
			if (this.isLocked(env) === true) {
				throw new Error("Cannot discard locked changes");
			}

			// A scheduled save request is pointless,
			// the discard throws the changes away anyway
			this.cancelSaving();

			// Start processing the request
			this.isProcessing = true;

			try {
				await this.request("discard", {}, env);

				// update the props for the current view
				panel.view.props.versions.changes = this.version("latest");

				this.emit("discard", {}, env);
			} catch (error) {
				// handle locked states
				if (error.key?.startsWith("error.content.lock")) {
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
		 * Whether there are any changes
		 */
		hasDiff(env = {}) {
			return length(this.diff(env)) > 0;
		},

		/**
		 * Whether content requests are still running or waiting
		 */
		isBusy() {
			return requests.isBusy();
		},

		/**
		 * Whether the api endpoint belongs to the current view
		 * @var {String} api
		 */
		isCurrent(env = {}) {
			const given = this.env(env);
			const current = this.env();
			return current.api === given.api && current.language === given.language;
		},

		/**
		 * Whether the current view is locked
		 * @param {String} api
		 */
		isLocked(env = {}) {
			return this.lock(env)?.isLocked ?? false;
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

			panel.view.props.versions.changes = {
				...this.version("changes"),
				...values
			};

			return panel.view.props.versions.changes;
		},

		/**
		 * Publishes any changes
		 */
		async publish(values = {}, env = {}) {
			if (this.isProcessing === true) {
				return;
			}

			if (this.isCurrent(env) === false) {
				throw new Error("Cannot publish content from another view");
			}

			// A scheduled save request is covered by the publish,
			// which submits the full state of the form again
			this.cancelSaving();

			// Start processing the request
			this.isProcessing = true;

			// Send updated values to API
			try {
				await this.request("publish", this.merge(values, env), env);

				// close the dialog if it is still open
				this.dialog?.close();

				// update the props for the current view
				panel.view.props.versions.latest = this.version("changes");

				this.emit("publish", { values }, env);
			} catch (error) {
				// handle locked states
				if (error.key?.startsWith("error.content.lock")) {
					return this.lockDialog(error.details);
				}

				throw error;
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Simplified request handler for all content API requests.
		 * Requests run one after another, a waiting request is only
		 * replaced by a newer one of the same type for the same view.
		 */
		async request(method = "save", values = {}, env = {}) {
			const { api, language } = this.env(env);

			const options = {
				headers: {
					"x-language": language
				}
			};

			if (method === "save" || method === "unlock") {
				options.silent = true;
			}

			return requests(`${method}:${api}:${language}`, () =>
				panel.api.post(api + "/changes/" + method, values, options)
			);
		},

		/**
		 * Saves any changes
		 *
		 * @returns {Boolean} Whether the changes have been written
		 */
		async save(values = {}, env = {}) {
			// A scheduled save request is superseded,
			// this one carries the full state of the form
			this.cancelSaving();

			try {
				await this.request("save", values, env);

				// close the dialog if it is still open
				this.dialog?.close();

				// update the lock timestamp
				if (this.isCurrent(env) === true) {
					this.lock(env).modified = new Date();
				}

				this.emit("save", { values }, env);

				return true;
			} catch (error) {
				// handle locked states
				if (error.key?.startsWith("error.content.lock")) {
					this.lockDialog(error.details);
					return false;
				}

				throw error;
			}
		},

		/**
		 * Releases the content lock without discarding changes.
		 * Called when the editor navigates away from the view.
		 *
		 * @returns {Boolean} Whether the lock has been released
		 */
		async unlock(env = {}) {
			// write the scheduled save first, the unlock runs after any
			// other save anyway. When the view got locked in the meantime,
			// the lock dialog already reports it and the lock stays.
			if ((await this.saveLazy.flush()) === false) {
				return false;
			}

			// fail silently because the lock will be released after the configured timeout
			await this.unlockPostRequest(env).catch(() => {});

			return true;
		},

		/**
		 * Sends the unlock request for the given view.
		 * Use sendBeacon for reliability during page unload. Browsers
		 * guarantee delivery even when the page is being closed.
		 */
		unlockBeaconRequest(env = {}) {
			const { api, language } = this.env(env);

			this.cancelSaving();

			// Build the URL with csrf and language as query params.
			// sendBeacon cannot set custom headers.
			const url = panel.url(`${panel.api.endpoint}${api}/changes/unlock`, {
				csrf: panel.api.csrf,
				language: language
			});

			// sendBeacon returns true if the request was successfully queued.
			if (navigator.sendBeacon(url) === true) {
				return;
			}

			// Fall back to a regular request if sendBeacon wasn't queued.
			// Fail silently to avoid blocking the unload event
			this.unlockPostRequest(env).catch(() => {});
		},

		/**
		 * Sends the unlock request for the given view
		 * as a regular API request
		 */
		async unlockPostRequest(env = {}) {
			return this.request("unlock", {}, env);
		},

		/**
		 * Updates the form values of the current view
		 *
		 * @returns {Boolean} Whether the changes have been written
		 */
		async update(values = {}, env = {}) {
			return await this.save(this.merge(values, env), env);
		},

		/**
		 * Updates the form values of the current view with a delay
		 */
		updateLazy(values = {}, env = {}) {
			// the scheduled save keeps its view, even when written after leaving it
			this.saveLazy(this.merge(values, env), this.env(env));
		},

		/**
		 * Returns a specific version of the content
		 * @param {String} versionId
		 * @returns {Object|undefined}
		 */
		version(versionId) {
			return this.versions()[versionId];
		},

		/**
		 * Returns all versions of the content
		 * @returns {Object}
		 */
		versions() {
			return panel.view.props.versions;
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
