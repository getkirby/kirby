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
	inheritAttrs: false,
	mixins: [Tree],
	props: {
		current: {
			default: "/site",
			type: String
		},
		items: {
			type: String
		}
	},
	async created() {
		if (this.items) {
			this.pages = await this.load();
		} else {
			this.pages = [
				{
					icon: "home",
					id: "/",
					label: this.$t("view.site"),
					hasChildren: true,
					children: "/site",
					open: true
				}
			];
		}
	},
	data() {
		return {
			pages: []
		};
	},
	methods: {
		async load() {
			const { data } = await this.$api.get(this.items + "/children", {
				select: "hasChildren,id,title",
				status: "all"
			});

			const pages = {};

			data.forEach((page) => {
				const id = "/" + page.id;
				const api = "/pages/" + this.$api.pages.id(page.id);

				pages[id] = {
					id: id,
					label: page.title,
					hasChildren: page.hasChildren,
					children: api,
					open: this.current?.includes(id) && this.current !== id
				};
			});

			return pages;
		},
		async toggle(page) {
			page.open = !page.open;
		}
	}
};
</script>
