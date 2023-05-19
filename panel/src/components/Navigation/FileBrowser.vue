<template>
	<div class="k-file-browser">
		<aside class="k-file-browser-tree">
			<k-page-tree :current="page?.id" @select="selectPage" />
		</aside>
		<div class="k-file-browser-items">
			<k-browser :items="files" :selected="selected" @select="selectFile" />
		</div>
	</div>
</template>

<script>
export default {
	props: {
		selected: {
			type: String
		}
	},
	data() {
		return {
			files: [],
			page: null
		};
	},
	methods: {
		selectFile(file) {
			this.$emit("select", file);
		},
		async selectPage(page) {
			this.page = page;

			const parent =
				page.id === "site://"
					? "/site/files"
					: "/pages/" + this.$api.pages.id(page.id) + "/files";

			const { data } = await this.$api.get(parent, {
				select: "filename,url,uuid"
			});

			this.files = data.map((file) => {
				return {
					label: file.filename,
					image: {
						src: file.url
					},
					id: file.uuid,
					value: file.uuid
				};
			});
		}
	}
};
</script>

<style>
.k-file-browser {
	display: grid;
	grid-template-columns: 15rem 1fr;
}

.k-file-browser-tree {
	padding: var(--spacing-2);
}
.k-file-browser-items {
	padding: var(--spacing-2);
	background: var(--color-white);
}
</style>
