import { reactive } from "vue";
import debounce from "@/helpers/debounce";

/**
 * @since 5.0.0
 */
export default (panel) => {
	return reactive({
		/**
		 * API endpoint to handle content changes
		 */
		get api() {
			return panel.view.props.api;
		},

		/**
		 * Updates the values of fields
		 *
		 * @param {Object} fields
		 * @param {any} value
		 */
		change(values) {
			panel.view.props.content = {
				...panel.view.props.originals,
				...values
			};

			this.hasUnsavedChanges = true;
		},

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

			this.isDiscarding = true;

			await panel.post(this.api + "/changes/discard");

			panel.view.props.content = panel.view.props.originals;
			panel.view.reload();
			this.isDiscarding = false;
		},
		/**
		 * Whether the model has changes that haven't been saved yet
		 *
		 * @returns {Boolean}
		 */
		hasUnsavedChanges: false,
		/**
		 * Whether the model has changes that haven't been published yet
		 *
		 * @returns {Boolean}
		 */
		get hasUnpublishedChanges() {
			return (
				JSON.stringify(panel.view.props.content) !==
				JSON.stringify(panel.view.props.originals)
			);
		},
		/**
		 * Whether the model is a draft
		 *
		 * @returns {Boolean}
		 */
		get isDraft() {
			return panel.view.props.model.status === "draft";
		},
		isDiscarding: false,
		/**
		 * Whether the content is currently locked by another user
		 *
		 * @returns {Boolean}
		 */
		get isLocked() {
			return this.lock?.state === "lock";
		},
		/**
		 * Global flag for any kind of writing or discarding operation
		 *
		 * @returns {Boolean}
		 */
		get isProcessing() {
			return this.isPublishing || this.isDiscarding || this.isSaving;
		},

		isPublishing: false,
		isSaving: false,

		/**
		 * Content lock state of the model
		 *
		 * @returns {Object|null|false}
		 */
		get lock() {
			const lock = panel.view.props.lock;

			if (!lock) {
				return false;
			}

			if (lock.state === null) {
				return null;
			}

			return {
				...lock.data,
				state: lock.state
			};
		},

		get originals() {
			return panel.view.props.originals;
		},

		/**
		 * Publishes any changes
		 */
		async publish() {
			if (this.isPublishing === true || this.isDiscarding === true) {
				return;
			}

			this.isPublishing = true;

			// Send updated values to API
			try {
				await panel.post(
					this.api + "/changes/publish",
					panel.view.props.content
				);

				panel.view.props.originals = panel.view.props.content;

				await panel.view.refresh();
			} finally {
				this.isPublishing = false;
			}

			panel.events.emit("model.update");
			panel.notification.success();
		},
		/**
		 * Saves any changes
		 */
		async save() {
			if (this.isProcessing === true) {
				return true;
			}

			this.isSaving = true;
			await panel.post(this.api + "/changes/save", panel.view.props.content);
			this.isSaving = false;
			this.hasUnsavedChanges = false;
		},
		/**
		 * Removes the content lock for the current user,
		 * e.g. when closing/leaving the model view
		 */
		async unlock() {},

		get values() {
			return panel.view.props.content;
		}
	});
};
