import { isAbortError } from "@/helpers/error";

export type SearchResponse = {
	results: unknown[] | null;
	pagination: Record<string, unknown>;
};

/**
 * @since 4.4.0
 */
export default function Search(panel: TODO) {
	return {
		controller: undefined as AbortController | undefined,
		requests: 0,

		/**
		 * Whether any search requests are ongoing
		 */
		get isLoading(): boolean {
			return this.requests > 0;
		},

		/**
		 * Opens the search dialog
		 */
		open(type: string): void {
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
		 */
		async query(
			type: string,
			query: string,
			options: { limit?: number; page?: number }
		): Promise<Prettify<SearchResponse> | undefined> {
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
				if (isAbortError(error) === false) {
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
}
