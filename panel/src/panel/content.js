import { length } from "@/helpers/object";
import { reactive, watch } from "vue";

/**
 * @since 5.0.0
 */
export default (panel) => {
	const content = reactive({
		/**
		 * API endpoint to handle content changes
		 */
		get api() {
			return panel.view.props.api;
		},

		/**
		 * Returns all fields and their values that
		 * have been changed but not yet saved
		 *
		 * @returns {Object}
		 */
		get changes() {
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
		async discard() {
			if (this.isProcessing === true) {
				return;
			}

			this.isProcessing = true;

			try {
				await panel.api.post(this.api + "/changes/discard");
				panel.view.props.content = panel.view.props.originals;
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Whether there are any changes
		 *
		 * @returns {Boolean}
		 */
		get hasChanges() {
			return length(this.changes) > 0;
		},

		/**
		 * Whether content is currently being discarded, saved or published
		 * @var {Boolean}
		 */
		isProcessing: false,

		/**
		 * Whether all content updates have been successfully sent to the backend
		 * @var {Boolean}
		 */
		isSaved: true,

		/**
		 * The last published state
		 *
		 * @returns {Object}
		 */
		get originals() {
			return panel.view.props.originals;
		},

		/**
		 * Publishes any changes
		 */
		async publish() {
			if (this.isProcessing === true) {
				return;
			}

			this.isProcessing = true;

			// Send updated values to API
			try {
				await panel.api.post(
					this.api + "/changes/publish",
					panel.view.props.content
				);

				panel.view.props.originals = panel.view.props.content;

				panel.events.emit("model.update");
				panel.notification.success();
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Saves any changes
		 */
		async save() {
			this.isProcessing = true;

			// ensure to abort unfinished previous save request
			// to avoid race conditions with older content
			this.saveAbortController?.abort();
			this.saveAbortController = new AbortController();

			try {
				await panel.api.post(
					this.api + "/changes/save",
					panel.view.props.content,
					{
						signal: this.saveAbortController.signal
					}
				);

				// update the last modification timestamp
				panel.view.props.lock.modified = new Date();

				this.isSaved = true;
				this.isProcessing = false;
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
		 * Updates the values of fields
		 *
		 * @param {Object} values
		 */
		update(values) {
			if (length(values) === 0) {
				return;
			}

			panel.view.props.content = {
				...panel.view.props.originals,
				...values
			};

			this.isSaved = false;
		},

		/**
		 * Returns all fields and values incl. changes
		 *
		 * @returns {Object}
		 */
		get values() {
			return panel.view.props.content;
		}
	});

	// watch for view changes and
	// trigger saving for changes that where
	// not sent to the server yet
	watch(
		() => content.api,
		() => {
			if (content.isSaved === false) {
				content.save();
			}
		}
	);

	// if user tries to close tab with changes not
	// sent to the server yet, trigger warning popup
	panel.events.on("beforeunload", (e) => {
		if (content.isSaved === false) {
			e.preventDefault();
			e.returnValue = "";
		}
	});

	return content;
};
