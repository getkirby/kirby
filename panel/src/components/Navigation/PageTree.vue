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
		current: {
			type: String
		},
		move: {
			type: String
		},
		root: {
			default: true,
			type: Boolean
		}
	},
	emits: ["select"],
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
			const items = await this.load(null);
			await this.open(items[0]);

			// if root is disabled, show the first level of children
			this.state = this.root ? items : items[0].children;

			// open current recursively, but only trigger from top-level PageTree
			if (this.current) {
				this.preselect(this.current);
			}
		}
	},
	methods: {
		findItem(id) {
			return this.state.find((item) => this.isItem(item, id));
		},
		isItem(item, target) {
			return (
				item.value === target || item.uuid === target || item.id === target
			);
		},
		async load(path) {
			return await this.$panel.get("site/tree", {
				query: {
					move: this.move ?? null,
					parent: path
				}
			});
		},
		async open(item) {
			if (!item) {
				return;
			}

			if (item.hasChildren === false) {
				return false;
			}

			item.loading = true;

			// children have not been loaded yet
			if (typeof item.children === "string") {
				item.children = await this.load(item.children);
			}

			item.open = true;
			item.loading = false;
		},
		async preselect(page) {
			// get array of parent uuids/ids
			const response = await this.$panel.get("site/tree/parents", {
				query: {
					page,
					root: this.root
				}
			});
			const parents = response.data;

			let tree = this;

			// go through all parents, try to find the matching item,
			// open it and pass forward the pointer to that tree component
			for (let index = 0; index < parents.length; index++) {
				const value = parents[index];
				const item = tree.findItem(value);

				if (!item) {
					return;
				}

				await this.open(item);
				tree = tree.$refs[value][0];
			}

			// find current page in deepest tree and trigger select listeners
			const item = tree.findItem(page);

			if (item) {
				this.$emit("select", item);
			}
		}
	}
};
</script>
