import debounce from "@/helpers/debounce.js";

/**
 * The Search mixin is intended for all components
 * that feature a query input that should trigger
 * running a search via a required `search` method.
 */
export default {
	props: {
		delay: {
			default: 200,
			type: Number
		},
		hasSearch: {
			default: true,
			type: Boolean
		}
	},
	data() {
		return {
			query: ""
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
		this.search = debounce(this.search, this.delay);
	},
	methods: {
		async search() {
			console.warn("Search mixin: Please implement a `search` method.");
		}
	}
};
