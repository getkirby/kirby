/**
 * @since 4.4.0
 */
export default (panel) => {
	return {
		controller: null,
		requests: 0,

		/**
		 * Whether any search requests are ongoing
		 */
		get isLoading() {
			return this.requests > 0;
		},

		/**
		 * Opens the search dialog
		 *
		 * @param {String} type
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
		 * to search for content in the Panel
		 *
		 * @param {String} type
		 * @param {Object} query
		 * @param {Object} options { limit, page }
		 * @returns {Object} { code, path, referrer, results, timestamp }
		 */
		async query(type, query, options) {
			// abort any previous ongoing search requests
			this.controller?.abort();
			this.controller = new AbortController();

			// skip API call if query empty
			if (query.length < 2) {
				return {
					results: null,
					pagination: {}
				};
			}

			this.requests++;

			try {
				const { search } = await panel.get(`/search/${type}`, {
					query: { query, ...options },
					signal: this.controller.signal
				});
				return search;
			} catch (error) {
				// if fails and not because request was aborted by subsequent request,
				// return empty response
				if (error.name !== "AbortError") {
					return {
						results: [],
						pagination: {}
					};
				}
			} finally {
				this.requests--;
			}
		}
	};
};
