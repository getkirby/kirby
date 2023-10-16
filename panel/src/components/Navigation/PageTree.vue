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
		move: {
			type: String
		}
	},
	data() {
		return {
			state: []
		};
	},
	async created() {
		if (this.items) {
			this.state = this.items;
		} else {
			// load top-level items (e.g. only site)
			const items = await this.load(null);
			await this.open(items[0]);

			// if root is disabled, show the first level of children
			this.state = this.root ? items : items[0].children;
		}
	},
	methods: {
		async load(path) {
			return await this.$panel.get("site/tree", {
				query: {
					move: this.move ?? null,
					parent: path
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
				item.children = await this.load(item.children);
			}

			this.$set(item, "open", true);
			this.$set(item, "loading", false);
		}
	}
};
</script>
