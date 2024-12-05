import { isObject } from "@/helpers/object";
import legacy from "./content.legacy.js";
import { reactive } from "vue";
import throttle from "@/helpers/throttle.js";

/**
 * @since 5.0.0
 */
export default (panel) => {
	const content = reactive({
		/**
		 * Returns an object with all changed fields
		 * @param {String} api
		 * @returns {Object}
		 */
		changes(api) {
			api ??= panel.view.props.api;

			// changes can only be computed for the current view
			if (this.isCurrent(api) === false) {
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
		async discard(api) {
			api ??= panel.view.props.api;

			if (this.isProcessing === true) {
				return;
			}

			// In the current view, we can use the existing
			// lock state to determine if we can discard
			if (this.isCurrent(api) === true && this.isLocked(api) === true) {
				throw new Error("Cannot discard locked changes");
			}

			this.isProcessing = true;

			try {
				await panel.api.post(api + "/changes/discard");

				// update the props for the current view
				if (this.isCurrent(api)) {
					panel.view.props.content = panel.view.props.originals;
				}

				panel.events.emit("content.discard", { api });
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Whether the api endpoint belongs to the current view
		 * @var {String} api
		 */
		isCurrent(api) {
			return panel.view.props.api === api;
		},

		/**
		 * Whether the current view is locked
		 * @param {String} api
		 */
		isLocked(api) {
			return this.lock(api ?? panel.view.props.api).isLocked;
		},

		/**
		 * Whether content is currently being discarded, saved or published
		 * @var {Boolean}
		 */
		isProcessing: false,

		/**
		 * Legacy content changes support
		 */
		legacy: legacy(panel),

		/**
		 * Get the lock state for the current view
		 * @param {String} api
		 */
		lock(api) {
			if (this.isCurrent(api ?? panel.view.props.api) === false) {
				throw new Error(
					"The lock state cannot be detected for content from another view"
				);
			}

			return panel.view.props.lock;
		},

		/**
		 * Merge new content changes with the
		 * original values and update the view props
		 */
		merge(values, api) {
			if (this.isCurrent(api ?? panel.view.props.api) === false) {
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
		async publish(values, api) {
			api ??= panel.view.props.api;

			if (this.isProcessing === true) {
				return;
			}

			// In the current view, we can use the existing
			// lock state to determine if changes can be published
			if (this.isCurrent(api) === true && this.isLocked(api) === true) {
				throw new Error("Cannot publish locked changes");
			}

			this.isProcessing = true;

			// Send updated values to API
			try {
				await panel.api.post(api + "/changes/publish", values);

				// close the retry dialog if it is still open
				this.dialog?.close();

				// update the props for the current view
				if (this.isCurrent(api)) {
					panel.view.props.originals = panel.view.props.content;
				}

				panel.events.emit("content.publish", { values, api });
			} catch (error) {
				this.retry("publish", error, panel.view.props.content, api);
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Opens a dialog with the error message
		 * to retry the given method.
		 */
		retry(method, error, values, api) {
			// log the error to the console to make it
			// easier to debug the issue
			console.error(error);

			// set the dialog instance
			this.dialog = panel.dialog;

			// show a dialog to the user to try again
			this.dialog.open({
				component: "k-text-dialog",
				props: {
					text: panel.t(`form.${method}.error`),
					cancelButton: panel.t("close"),
					submitButton: {
						icon: "refresh",
						text: panel.t("retry")
					}
				},
				on: {
					close: () => {
						this.dialog = null;
					},
					submit: async () => {
						this.dialog.isLoading = true;

						// try again with the latest state in the props
						await this[method](panel.view.props.content, api);

						// make sure the dialog is closed if the request was successful
						this.dialog?.close();

						// give a more reassuring longer success notification
						panel.notification.success(panel.t(`form.${method}.success`));
					}
				}
			});
		},

		/**
		 * Saves any changes
		 */
		async save(values, api) {
			api ??= panel.view.props.api;

			if (this.isCurrent(api) === true && this.isLocked(api) === true) {
				throw new Error("Cannot save locked changes");
			}

			this.isProcessing = true;

			// ensure to abort unfinished previous save request
			// to avoid race conditions with older content
			this.saveAbortController?.abort();
			this.saveAbortController = new AbortController();

			try {
				await panel.api.post(api + "/changes/save", values, {
					signal: this.saveAbortController.signal,
					silent: true
				});

				this.isProcessing = false;

				// close the retry dialog if it is still open
				this.dialog?.close();

				// update the lock timestamp
				if (this.isCurrent(api) === true) {
					panel.view.props.lock.modified = new Date();
				}

				panel.events.emit("content.save", { api, values });
			} catch (error) {
				// silent aborted requests, but throw all other errors
				if (error.name !== "AbortError") {
					this.isProcessing = false;
					this.retry("save", error, panel.view.props.content, api);
				}
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
		async update(values, api) {
			return await this.save(this.merge(values, api), api);
		},

		/**
		 * Updates the form values of the current view with a delay
		 */
		updateLazy(values, api) {
			this.saveLazy(this.merge(values, api), api);
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
