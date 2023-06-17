import debounce from "@/helpers/debounce.js";

/**
 * The Search mixin is intended for all components
 * that feature a query input that should trigger
 * running a search via a required `search` method.
 */
export default {
	props: {
		hasSearch: {
			type: Boolean,
			default: true
		}
	},
	data() {
		return {
			query: null
		};
	},
	watch: {
		query() {
			if (this.hasSearch !== false) {
				this.search();
			}
		}
	},
	created() {
		this.search = debounce(this.search, 200);
	},
	methods: {
		async search() {
			console.warn("Search mixin: Please implement a `search` method.");
		}
	}
};
