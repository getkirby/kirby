<script>
import Tree from "./Tree.vue";

/**
 * @displayName PageTree
 * @since 4.0.0
 */
export default {
	name: "k-page-tree",
	extends: Tree,
	inheritAttrs: false,
	props: {
		root: {
			default: true,
			type: Boolean
		},
		current: {
			type: String
		},
		/**
		 * Items per folder to load at once
		 */
		limit: {
			type: Number
		},
		move: {
			type: String
		}
	},
	data() {
		return {
			state: []
		};
	},
	async mounted() {
		if (this.items) {
			this.state = this.items;
		} else {
			// load top-level items (e.g. only site)
			const { items } = await this.load(null);
			await this.open(items[0]);

			// if root is disabled, show the first level of children
			this.state = this.root ? items : items[0].children;
		}
	},
	methods: {
		hasPaginate(item) {
			if (!item.pagination) {
				return false;
			}

			return (
				item.pagination.page * item.pagination.limit < item.pagination.total
			);
		},
		async load(path, page) {
			return await this.$panel.get("site/tree", {
				query: {
					move: this.move ?? null,
					parent: path,
					page: page ?? 1,
					limit: this.limit ?? null
				}
			});
		},
		async open(item) {
			if (item.hasChildren === false) {
				return false;
			}

			this.$set(item, "loading", true);

			// children have not been loaded yet
			if (typeof item.children === "string") {
				const { items, pagination } = await this.load(item.children);
				item.endpoint = item.children;
				item.children = items;
				item.pagination = pagination;
			}

			this.$set(item, "open", true);
			this.$set(item, "loading", false);
		},
		async paginate(item) {
			this.$set(item, "loading", true);

			// children have not been loaded yet
			const { items, pagination } = await this.load(
				item.endpoint,
				item.pagination.page + 1
			);
			this.$set(item, "children", [...item.children, ...items]);
			this.$set(item, "pagination", pagination);

			this.$set(item, "loading", false);
		}
	}
};
</script>
