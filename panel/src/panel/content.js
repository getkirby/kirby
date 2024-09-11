import { reactive, set, del } from "vue";
import { clone, length } from "@/helpers/object.js";

/**
 * @since 5.0.0
 */
export default (panel) => {
	return reactive({
		/**
		 * API endpoint to handle content changes
		 */
		api: null,

		/**
		 * All fields and their values that
		 * have been changed but not yet published
		 */
		changes: {},

		/**
		 * All fields and their already published values
		 */
		published: {},

		/**
		 * Removes all unpublished changes
		 */
		async discard() {
			if (this.isProcessing === true) {
				return;
			}

			this.isDiscarding = true;
			await panel.post(this.api + "/changes/discard");
			this.changes = {};
			panel.view.reload();
			this.isDiscarding = false;
		},
		/**
		 * Whether the model has changes that haven't been saved yet
		 *
		 * @returns {Boolean}
		 */
		get hasUnsavedChanges() {
			return false;
		},
		/**
		 * Whether the model has changes that haven't been published yet
		 *
		 * @returns {Boolean}
		 */
		get hasUnpublishedChanges() {
			return length(this.changes) > 0;
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
		/**
		 * Publishes any changes
		 */
		async publish(e) {
			e?.preventDefault?.();

			if (this.isPublishing === true || this.isDiscarding === true) {
				return;
			}

			this.isPublishing = true;

			// Send updated values to API
			try {
				await panel.post(this.api + "/changes/publish", this.values);
				await panel.view.reload();
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
			await panel.post(this.api + "/changes/save", this.values);
			this.isSaving = false;
		},
		/**
		 * Updates the values of fields
		 *
		 * @param {Object} fields
		 * @param {any} value
		 */
		set(values) {
			this.changes = values;
			this.save();
		},
		/**
		 * Removes the content lock for the current user,
		 * e.g. when closing/leaving the model view
		 */
		async unlock() {},
		/**
		 * Returns all fields and values incl. changes
		 *
		 * @returns {Object}
		 */
		get values() {
			return {
				...this.published,
				...this.changes
			};
		}
	});
};
