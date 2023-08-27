<script>
import PageTree from "./PageTree.vue";

export default {
	name: "k-page-move-tree",
	extends: PageTree,
	props: {
		target: {
			required: true,
			type: String
		}
	},
	methods: {
		async load(path) {
			const { data } = await this.$api.get(path + "/children", {
				target: this.target,
				select: "canBeMovedTo,hasChildren,hasDrafts,id,panelImage,title,uuid",
				status: "all"
			});

			const pages = {};

			data.forEach((page) => {
				const id = page[this.identifier];

				pages[id] = {
					id,
					disabled: !page.canBeMovedTo,
					icon: page.panelImage.icon,
					label: page.title,
					hasChildren: page.hasChildren || page.hasDrafts,
					children: "/pages/" + this.$api.pages.id(page.id),
					open: false
				};
			});

			return pages;
		}
	}
};
</script>

<style>
.k-tree-folder[disabled] {
	--tree-color-text: var(--color-gray-600);
}
</style>
