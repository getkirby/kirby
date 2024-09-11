import { reactive } from "vue";
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
		discard() {
			this.changes = {};
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
		isPublishing: false,
		isSaving: false,
		/**
		 * Whether the content is currently locked by another user
		 *
		 * @returns {Boolean}
		 */
		get isLocked() {
			return this.lock?.state === "lock";
		},
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

			if (this.isPublishing === true) {
				return;
			}

			this.isPublishing = true;

			// Send updated values to API
			try {
				await window.panel.api.patch(this.api, this.values);
				this.published = this.values;
				this.changes = {};
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
			this.isSaving = true;
			// …
			this.isSaving = false;
		},
		/**
		 * Updates the values of fields
		 *
		 * @param {Object} fields
		 * @param {any} value
		 */
		set(fields) {
			const changes = {
				...clone(this.published),
				...clone(this.changes),
				...fields
			};

			const a = JSON.stringify(this.published);
			const b = JSON.stringify(changes);

			if (a === b) {
				this.changes = {};
			} else {
				this.changes = changes;
			}
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
