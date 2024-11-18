import { length } from "@/helpers/object";
import { reactive, set } from "vue";

/**
 * @since 5.0.0
 */
export default (panel) => {
	return reactive({
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
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Whether the api endpoint belongs to the current view
		 * @var {Boolean}
		 */
		isCurrent(api) {
			return panel.view.props.api === api;
		},

		/**
		 * Whether the current view is locked
		 * @var {Boolean}
		 */
		isLocked(api = panel.view.props.api) {
			if (this.isCurrent(api) === false) {
				throw new Error(
					"The lock state cannot be detected for content from in another view"
				);
			}

			return panel.view.props.lock.isLocked;
		},

		/**
		 * Whether content is currently being discarded, saved or published
		 * @var {Boolean}
		 */
		isProcessing: false,

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
			this.update(api, values);

			// Send updated values to API
			try {
				await panel.api.post(api + "/changes/publish", values);

				if (this.isCurrent(api)) {
					panel.view.props.originals = panel.view.props.content;
				}
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

				// update the lock info
				if (this.isCurrent(api) === true) {
					panel.view.props.lock.modified = new Date();
				}
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
};
