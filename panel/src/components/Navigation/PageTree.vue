<template>
	<k-tree
		:current="current"
		:items="pages"
		:level="level"
		class="k-page-tree"
		element="k-page-tree"
		@select="select"
		@toggle="toggle"
	/>
</template>

<script>
import Tree from "./Tree.vue";

export default {
	mixins: [Tree],
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
		identifier: {
			default: "uuid",
			type: String
		},
		items: {
			type: String
		}
	},
	data() {
		return {
			pages: []
		};
	},
	async created() {
		if (this.items) {
			this.pages = await this.load(this.items);
		} else if (this.root === false) {
			this.pages = await this.load("/site");
		} else {
			this.pages = [
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
				select: "hasChildren,id,title,uuid",
				status: "all"
			});

			const pages = {};

			data.forEach((page) => {
				const id = page[this.identifier];
				const api = "/pages/" + this.$api.pages.id(page.id);

				pages[id] = {
					id: id,
					label: page.title,
					hasChildren: page.hasChildren,
					children: api,
					open: false
				};
			});

			return pages;
		},
		async toggle(page) {
			page.open = !page.open;
			this.$emit("toggleBranch", page);
		}
	}
};
</script>
