import { length } from "@/helpers/object";
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
		changes(api = panel.view.props.api) {
			if (this.isCurrent(api) === false) {
				throw new Error("Cannot get changes from another view");
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

		/**
		 * Removes all unpublished changes
		 */
		async discard(api = panel.view.props.api) {
			if (this.isProcessing === true) {
				return;
			}

			if (this.isCurrent(api) === true && this.isLocked(api) === true) {
				throw new Error("Cannot discard locked changes");
			}

			this.isProcessing = true;

			try {
				await panel.api.post(api + "/changes/discard");

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
		isLocked(api = panel.view.props.api) {
			return this.lock(api).isLocked;
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
		lock(api = panel.view.props.api) {
			if (this.isCurrent(api) === false) {
				throw new Error(
					"The lock state cannot be detected for content from in another view"
				);
			}

			return panel.view.props.lock;
		},

		/**
		 * Publishes any changes
		 */
		async publish(values, api = panel.view.props.api) {
			if (this.isProcessing === true) {
				return;
			}

			if (this.isCurrent(api) === true && this.isLocked(api) === true) {
				throw new Error("Cannot publish locked changes");
			}

			this.isProcessing = true;

			// Send updated values to API
			try {
				await panel.api.post(api + "/changes/publish", values);

				if (this.isCurrent(api)) {
					panel.view.props.originals = panel.view.props.content;
				}

				panel.events.emit("content.publish", { api, values });
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Saves any changes
		 */
		async save(values, api = panel.view.props.api) {
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

				// update the lock timestamp
				if (this.isCurrent(api) === true) {
					panel.view.props.lock.modified = new Date();
				}

				panel.events.emit("content.save", { api, values });
			} catch (error) {
				// silent aborted requests, but throw all other errors
				if (error.name !== "AbortError") {
					this.isProcessing = false;
					throw error;
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
		update(values, api = panel.view.props.api) {
			if (length(values) === 0) {
				return;
			}

			if (this.isCurrent(api) === false) {
				throw new Error("The content in another view cannot be updated");
			}

			panel.view.props.content = {
				...panel.view.props.originals,
				...values
			};
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
