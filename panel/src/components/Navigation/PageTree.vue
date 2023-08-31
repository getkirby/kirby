<script>
import Tree from "./Tree.vue";

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
			default: "/site",
			type: String
		},
		/**
		 * @values `id`, `uuid`
		 */
		identifier: {
			default: "uuid",
			type: String,
			validator: (value) => ["id", "uuid"].includes(value)
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
	created() {
		if (this.items) {
			this.state = this.items;
		} else {
			this.state = [
				{
					icon: "home",
					id: "site://",
					label: this.$t("view.site"),
					hasChildren: true,
					children: "/site",
					open: false
				}
			];

			this.open(this.state[0]);
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
