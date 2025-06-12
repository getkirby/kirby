<template>
	<div class="k-file-browser" :data-view="view">
		<div class="k-file-browser-layout">
			<aside ref="tree" class="k-file-browser-tree">
				<k-page-tree
					:current="page?.value ?? opened"
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
			<div class="k-file-browser-pagination" @click.stop>
				<k-pagination
					v-if="pagination"
					v-bind="pagination"
					:details="true"
					@paginate="paginate"
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
		limit: {
			default: 50,
			type: Number
		},
		/**
		 * A page uuid/id which should be preselected
		 */
		opened: {
			type: String
		},
		selected: {
			type: String
		}
	},
	emits: ["select"],
	data() {
		return {
			files: [],
			page: null,
			pagination: null,
			view: this.opened ? "files" : "tree"
		};
	},
	methods: {
		paginate(pagination) {
			this.selectPage(this.page, pagination.page);
		},
		selectFile(file) {
			this.$emit("select", file);
		},
		async selectPage(model, page = 1) {
			this.page = model;

			const parent =
				model.id === "/"
					? "/site/files"
					: "/pages/" + this.$api.pages.id(model.id) + "/files";

			const { data, pagination } = await this.$api.get(parent, {
				select: "filename,id,panelImage,url,uuid",
				limit: this.limit,
				page: page
			});

			this.pagination = pagination;

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
:root {
	--file-browser-items-color-back: light-dark(
		var(--color-gray-100),
		var(--panel-color-back)
	);
}

.k-file-browser {
	container-type: inline-size;
	overflow: hidden;
}

.k-file-browser-layout {
	display: grid;
	grid-template-columns: minmax(10rem, 15rem) 1fr;
	grid-template-rows: 1fr auto;
	grid-template-areas:
		"tree items"
		"tree pagination";
}

.k-file-browser-tree {
	grid-area: tree;
	padding: var(--spacing-2);
	border-right: 1px solid var(--color-border);
}
.k-file-browser-items {
	grid-area: items;
	padding: var(--spacing-2);
	background: var(--file-browser-items-color-back);
}
.k-file-browser-back-button {
	display: none;
}

.k-file-browser-pagination {
	background: var(--file-browser-items-color-back);
	padding: var(--spacing-2);
	display: flex;
	justify-content: end;
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
		background: light-dark(var(--color-gray-200), var(--color-gray-800));
		border-radius: var(--rounded);
	}
	.k-file-browser-tree {
		border-right: 0;
	}
	.k-file-browser-pagination {
		justify-content: start;
	}
	.k-file-browser[data-view="files"] .k-file-browser-layout {
		grid-template-rows: 1fr auto;
		grid-template-areas:
			"items"
			"pagination";
	}
	.k-file-browser[data-view="files"] .k-file-browser-tree {
		display: none;
	}
	.k-file-browser[data-view="tree"] .k-file-browser-items,
	.k-file-browser[data-view="tree"] .k-file-browser-pagination {
		display: none;
	}
}
</style>
