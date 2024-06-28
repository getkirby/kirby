export default (panel) => {
	return {
		controller: null,

		/**
		 * Open the search dialog
		 * @param {String} type to preselect
		 */
		open(type) {
			// close menu on mobile
			panel.menu.escape();

			panel.dialog.open({
				component: "k-search-dialog",
				props: {
					type
				}
			});
		},

		/**
		 * Use one of the installed search types
		 * to search for content in the panel
		 *
		 * @param {String} type
		 * @param {Object} query
		 * @param {Object} options { limit, page }
		 * @returns {Object} { code, path, referrer, results, timestamp }
		 */
		async search(type, query, options) {
			// open the search dialog
			if (!query) {
				return this.open(type);
			}

			// abort previous search requests
			this.controller?.abort();
			this.controller = new AbortController();

			const { $search } = await panel.get(`/search/${type}`, {
				query: { query, ...options },
				signal: this.controller.signal
			});

			return $search;
		}
	};
};
