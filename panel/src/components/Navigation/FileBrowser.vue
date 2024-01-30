<template>
	<div class="k-file-browser" :data-view="view">
		<div class="k-file-browser-layout">
			<aside ref="tree" class="k-file-browser-tree">
				<k-page-tree
					:current="page?.value"
					@select="selectPage"
					@toggleBranch="togglePage"
				/>
			</aside>
			<div ref="items" class="k-file-browser-items">
				<k-button
					class="k-file-browser-back-button"
					icon="angle-left"
					:text="page?.label"
					@click="view = 'tree'"
				/>
				<k-browser
					v-if="files.length"
					:items="files"
					:selected="selected"
					@select="selectFile"
				/>
			</div>
		</div>
	</div>
</template>

<script>
/**
 * @since 4.0.0
 */
export default {
	props: {
		selected: {
			type: String
		}
	},
	emits: ["select"],
	data() {
		return {
			files: [],
			page: null,
			view: "tree"
		};
	},
	methods: {
		selectFile(file) {
			this.$emit("select", file);
		},
		async selectPage(page) {
			this.page = page;

			const parent =
				page.id === "/"
					? "/site/files"
					: "/pages/" + this.$api.pages.id(page.id) + "/files";

			const { data } = await this.$api.get(parent, {
				select: "filename,id,panelImage,url,uuid"
			});

			this.files = data.map((file) => {
				return {
					label: file.filename,
					image: file.panelImage,
					id: file.id,
					url: file.url,
					uuid: file.uuid,
					value: file.uuid ?? file.url
				};
			});

			this.view = "files";
		},
		async togglePage() {
			await this.$nextTick();
			this.$refs.tree.scrollIntoView({
				behaviour: "smooth",
				block: "nearest",
				inline: "nearest"
			});
		}
	}
};
</script>

<style>
.k-file-browser {
	container-type: inline-size;
	overflow: hidden;
}

.k-file-browser-layout {
	display: grid;
	grid-template-columns: minmax(10rem, 15rem) 1fr;
}

.k-file-browser-tree {
	padding: var(--spacing-2);
	border-right: 1px solid var(--color-gray-300);
}
.k-file-browser-items {
	padding: var(--spacing-2);
	background: var(--color-gray-100);
}
.k-file-browser-back-button {
	display: none;
}

@container (max-width: 30rem) {
	.k-file-browser-layout {
		grid-template-columns: minmax(0, 1fr);
		min-height: 10rem;
	}
	.k-file-browser-back-button {
		width: 100%;
		height: var(--height-sm);
		display: flex;
		align-items: center;
		justify-content: flex-start;
		padding-inline: 0.25rem;
		margin-bottom: 0.5rem;
		background: var(--color-gray-200);
		border-radius: var(--rounded);
	}
	.k-file-browser-tree {
		border-right: 0;
	}
	.k-file-browser[data-view="files"] .k-file-browser-tree {
		display: none;
	}
	.k-file-browser[data-view="tree"] .k-file-browser-items {
		display: none;
	}
}
</style>
