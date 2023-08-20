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
		items: {
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
			this.state = await this.load(this.items);
		} else if (this.root === false) {
			this.state = await this.load("/site");
		} else {
			this.state = [
				{
					icon: "home",
					id: "site://",
					label: this.$t("view.site"),
					hasChildren: true,
					children: "/site",
					open: true
				}
			];
		}
	},
	methods: {
		async load(path) {
			const { data } = await this.$api.get(path + "/children", {
				select: "hasChildren,id,panelImage,title,uuid",
				status: "all"
			});

			const pages = {};

			for (const page of data) {
				const id = page[this.identifier];
				pages[id] = {
					id,
					icon: page.panelImage.icon,
					label: page.title,
					hasChildren: page.hasChildren,
					children: "/pages/" + this.$api.pages.id(page.id),
					open: false
				};
			}

			return pages;
		},
		toggle(page) {
			page.open = !page.open;
			this.$emit("toggleBranch", page);
		}
	}
};
</script>
