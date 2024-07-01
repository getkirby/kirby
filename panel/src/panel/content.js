/**
 * @since 5.0.0
 */
export default (panel) => {
	return {
		/**
		 * Returns all fields and their values that
		 * have been changed but not yet published
		 *
		 * @returns {Object}
		 */
		get changes() {
			return panel.app.$store.getters["content/changes"]();
		},
		/**
		 * Removes all unpublished changes
		 */
		discard() {
			panel.app.$store.dispatch("content/revert");
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
			return panel.app.$store.getters["content/hasChanges"]();
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
		 * Content lock state of the model
		 *
		 * @returns {Object|null|false}
		 */
		get lock() {
			const lock = panel.view.props.lock;

			if (lock === false) {
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

			this.isPublishing = true;
			await panel.app.$store.dispatch("content/save");
			panel.events.emit("model.update");
			panel.notification.success();
			this.isPublishing = false;
		},
		/**
		 * Returns all fields and their already published values
		 *
		 * @returns {Object}
		 */
		get published() {
			return panel.app.$store.getters["content/originals"]();
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
		 * Updates the value of fields/a field
		 *
		 * @param {String|Object} fields
		 * @param {any} value
		 */
		set(fields, value) {
			if (typeof fields === "string") {
				fields = { [fields]: value };
			}

			panel.app.$store.dispatch("content/update", [null, fields]);
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
	};
};
