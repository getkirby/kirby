import { reactive } from "vue";

/**
 * @since 5.0.0
 */
export default (panel) => {
	return reactive({
		/**
		 * Removes all unpublished changes
		 */
		async discard(api) {
			if (this.isProcessing === true) {
				return;
			}

			this.isProcessing = true;

			try {
				await panel.api.post(api + "/changes/discard");
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Whether content is currently being discarded, saved or published
		 * @var {Boolean}
		 */
		isProcessing: false,

		/**
		 * Publishes any changes
		 */
		async publish(api, values) {
			if (this.isProcessing === true) {
				return;
			}

			this.isProcessing = true;

			// Send updated values to API
			try {
				await panel.api.post(api + "/changes/publish", values);
			} finally {
				this.isProcessing = false;
			}
		},

		/**
		 * Saves any changes
		 */
		async save(api, values) {
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
		saveAbortController: null
	});
};
