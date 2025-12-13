<template>
	<k-panel class="k-panel-inside k-preview-view" :data-mode="mode">
		<header class="k-preview-view-header">
			<k-button-group>
				<k-button
					:link="back"
					:responsive="true"
					:title="$t('back')"
					icon="angle-left"
					size="sm"
					variant="filled"
				>
				</k-button>
				<k-button
					class="k-preview-view-title"
					:icon="$panel.isLoading ? 'loader' : 'title'"
					:dropdown="true"
					@click="$refs.tree.toggle()"
				>
					{{ title }}
				</k-button>
				<k-dropdown ref="tree" theme="dark" class="k-preview-view-tree">
					<k-page-tree :current="id" @click.stop @select="onNavigate" />
				</k-dropdown>
			</k-button-group>

			<k-button-group
				v-if="mode === 'form'"
				layout="collapsed"
				class="k-preview-viewport"
			>
				<k-button
					:current="view === 'small'"
					icon="mobile"
					size="sm"
					variant="filled"
					@click="viewport('small')"
				/>
				<k-button
					:current="view === 'medium'"
					icon="tablet"
					size="sm"
					variant="filled"
					@click="viewport('medium')"
				/>
				<k-button
					:current="view === 'large'"
					icon="display"
					size="sm"
					variant="filled"
					@click="viewport('large')"
				/>
			</k-button-group>

			<k-button-group>
				<k-view-buttons :buttons="buttons" />
			</k-button-group>
		</header>

		<main class="k-preview-view-grid" :data-view="view">
			<template v-if="mode === 'form'">
				<k-preview-browser
					ref="browser"
					v-bind="browserProps('changes')"
					@discard="onDiscard"
					@navigate="onNavigate"
					@submit="onSubmit"
				/>

				<k-preview-form
					:api="api"
					:blueprint="blueprint"
					:content="content"
					:tabs="tabs"
					:tab="tab"
					@input="onInput"
					@submit="onSubmit"
				/>
			</template>
			<template v-else-if="mode === 'compare'">
				<k-preview-browser
					v-bind="browserProps('latest')"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
				<k-preview-browser
					v-bind="browserProps('changes')"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
			<template v-else>
				<k-preview-browser
					v-bind="browserProps(mode)"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
		</main>
	</k-panel>
</template>

<script>
import ModelView from "@/components/Views/ModelView.vue";

export default {
	extends: ModelView,
	props: {
		back: String,
		mode: String,
		src: Object,
		title: String
	},
	data() {
		return {
			view: "large"
		};
	},
	mounted() {
		this.$events.on("keydown.esc", this.exit);
		this.$events.on("content.save", this.onChanges);
		this.$events.on("model.update", this.onChanges);
		this.$events.on("page.changeTitle", this.onChanges);
		this.$events.on("page.sort", this.onChanges);
		this.$events.on("file.sort", this.onChanges);
	},
	unmounted() {
		this.$events.off("keydown.esc", this.exit);
		this.$events.off("content.save", this.onChanges);
		this.$events.off("model.update", this.onChanges);
		this.$events.off("page.changeTitle", this.onChanges);
		this.$events.off("page.sort", this.onChanges);
		this.$events.off("file.sort", this.onChanges);
	},
	methods: {
		browserProps(mode) {
			return {
				editor: this.editor,
				hasDiff: this.hasDiff,
				isLocked: this.isLocked,
				isProcessing: this.isSaving,
				modified: this.modified,
				label: this.$t("version." + mode),
				src: this.src[mode],
				mode: mode
			};
		},
		exit() {
			if (this.$panel.overlays().length > 0) {
				return;
			}

			this.$panel.view.open(this.link);
		},
		onChanges() {
			this.$refs.browser.reload();
		},
		onNavigate(page) {
			if (page.id === this.id) {
				return;
			}

			this.$refs.tree.close();

			if (page.id === "/") {
				return this.$panel.view.open("site/preview/" + this.mode);
			}

			const url = this.$api.pages.url(page.id, "preview/" + this.mode);
			this.$panel.view.open(url);
		},
		viewport(size) {
			this.view = size;
		}
	}
};
</script>

<style>
.k-preview-view {
	position: fixed;
	inset: 0;
	height: 100%;
	display: grid;
	grid-template-rows: auto 1fr;
}
.k-preview-view-header {
	container-type: inline-size;
	display: flex;
	gap: var(--spacing-2);
	justify-content: space-between;
	align-items: center;
	padding: var(--spacing-3);
}
.k-preview-view-tree {
	--tree-branch-color-back: transparent;
	--tree-branch-hover-color-back: var(--color-gray-800);
	--tree-branch-selected-color-back: var(--color-blue-800);

	width: 20rem;
}

.k-preview-view-grid {
	display: flex;
	justify-content: center;
	padding: var(--spacing-3);
	padding-top: 0;
	gap: var(--spacing-3);
	max-height: calc(100vh - 56px);
}
@media screen and (max-width: 60rem) {
	.k-preview-view-grid {
		flex-direction: column;
	}
	.k-preview-view-title {
		display: none;
	}
}
.k-preview-view :where(.k-preview-browser, .k-preview-form) {
	flex-grow: 1;
	flex-basis: 50%;
	transition: flex-basis 0.1s;
}

.k-preview-view-grid[data-view="small"] .k-preview-browser {
	flex-basis: 33.33%;
}
.k-preview-view-grid[data-view="small"] .k-preview-form {
	flex-basis: 66.66%;
}
.k-preview-view-grid[data-view="medium"] .k-preview-browser {
	flex-basis: 45%;
}
.k-preview-view-grid[data-view="medium"] .k-preview-form {
	flex-basis: 55%;
}
.k-preview-view-grid[data-view="large"] .k-preview-browser {
	flex-basis: 66.66%;
}
.k-preview-view-grid[data-view="large"] .k-preview-form {
	flex-basis: 33.33%;
}

.k-preview-viewport .k-button[aria-current="true"] {
	--button-color-back: var(--color-blue-350);
	--button-color-text: var(--color-blue-800);
}
</style>
