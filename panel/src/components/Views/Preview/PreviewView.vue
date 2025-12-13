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
					<k-page-tree :current="id" @click.stop @select="onTreeNavigate" />
				</k-dropdown>
			</k-button-group>

			<k-button-group
				v-if="mode !== 'compare'"
				layout="collapsed"
				class="k-preview-viewport"
			>
				<k-button
					v-for="(viewport, size) in viewports"
					:key="size"
					:current="view === size"
					:icon="viewport.icon"
					:theme="view === size ? 'info' : null"
					size="sm"
					variant="filled"
					@click="onViewport(size)"
				/>
			</k-button-group>

			<k-button-group>
				<k-view-buttons :buttons="buttons" />
			</k-button-group>
		</header>

		<main
			class="k-preview-view-grid"
			:data-view="view"
			:style="`--preview-width: ${viewports[view].width}`"
		>
			<template v-if="mode === 'form'">
				<k-preview-browser
					ref="browser"
					v-bind="browserProps('form')"
					@discard="onDiscard"
					@navigate="onBrowserNavigate"
					@reload="onReload"
					@submit="onSubmit"
				/>

				<k-preview-form
					v-bind="browserProps('form')"
					:api="api"
					:blueprint="blueprint"
					:content="content"
					:diff="diff"
					:tabs="tabs"
					:tab="tab"
					@discard="onDiscard"
					@input="onInput"
					@navigate="onViewNavigate"
					@submit="onSubmit"
				/>
			</template>
			<template v-else-if="mode === 'compare'">
				<k-preview-browser
					v-bind="browserProps('latest')"
					@discard="onDiscard"
					@navigate="onBrowserNavigate"
					@submit="onSubmit"
				/>
				<k-preview-browser
					v-bind="browserProps('changes')"
					@discard="onDiscard"
					@navigate="onBrowserNavigate"
					@submit="onSubmit"
				/>
			</template>
			<template v-else>
				<k-preview-browser
					v-bind="browserProps(mode)"
					@discard="onDiscard"
					@navigate="onBrowserNavigate"
					@submit="onSubmit"
				/>
			</template>
		</main>

		<k-panel-notifications />
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
		title: String,
		viewports: {
			type: Object,
			default: () => ({
				small: { icon: "mobile", width: "390px" },
				medium: { icon: "tablet", width: "820px" },
				large: { icon: "display", width: "100%" }
			})
		}
	},
	data() {
		const viewport = sessionStorage.getItem("kirby$preview$viewport");

		return {
			view:
				viewport && this.viewports[viewport]
					? viewport
					: Object.keys(this.viewports).pop()
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
			const src = mode === "form" ? "changes" : mode;

			return {
				editor: this.editor,
				hasDiff: this.hasDiff,
				isLocked: this.isLocked,
				isProcessing: this.isSaving,
				modified: this.modified,
				label: this.$t("version." + src),
				src: this.src[src],
				mode: mode
			};
		},
		/**
		 * Closes the preview view to the corresponding page/site view
		 */
		exit() {
			// ignore if dialogs/drawers are still open
			if (this.$panel.overlays().length > 0) {
				return;
			}

			this.$panel.view.open(this.link);
		},
		/**
		 * Reload the browser to reflect updated content
		 */
		onChanges() {
			this.$refs.browser.reload();
		},
		/**
		 * Reloads the view or just the browser src
		 * triggered by navigation in the browser
		 */
		onBrowserNavigate({ browser = null, view = null }) {
			this.$panel.view.reload({ query: { browser, view } });
		},
		/**
		 * Reloads the preview view for the current ID
		 */
		onReload() {
			this.onTreeNavigate({ id: this.id }, true);
		},
		/**
		 * Opens the right preview view for the page tree dropdown
		 */
		onTreeNavigate(page, force = false) {
			if (page.id === this.id && force === false) {
				return;
			}

			this.$refs.tree?.close();

			const id = page.id === "/" ? "site" : page.id;
			const url = this.$api.pages.url(id, "preview/" + this.mode);
			this.onViewNavigate(url);
		},
		/**
		 * Opens a new preview view
		 */
		async onViewNavigate(url) {
			// when the view changes via a link in the preview form (e.g. a section),
			// store the current browser src and scroll position, open the view
			// then offer the browser to restore src and scroll position
			// (which the browser will decide based on whether it's pinned or not)
			const browser = this.$refs.browser?.store();
			await this.$panel.view.open(url);
			this.$refs.browser?.restore(browser);
		},
		/**
		 * Sets a viewport size and remembers it in sessionStorage
		 */
		onViewport(viewport) {
			this.view = viewport;
			sessionStorage.setItem("kirby$preview$viewport", viewport);
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
	.k-preview-view-title,
	.k-preview-viewport {
		display: none;
	}
}
.k-preview-view :where(.k-preview-browser, .k-preview-form) {
	flex-grow: 1;
	flex-basis: 50%;
	transition: flex-basis 0.1s;
}

@media screen and (min-width: 60rem) {
	.k-preview-view:not([data-mode="compare"]) .k-preview-browser {
		flex: 0 0 calc(var(--preview-width) + 2px);
	}
	.k-preview-view[data-mode="form"] .k-preview-browser {
		flex: 0 0 min(calc(var(--preview-width) + 2px), 70vw);
	}
	.k-preview-view[data-mode="form"] .k-preview-form {
		flex: 1 1 0;
		min-width: 0;
	}
}

.k-preview-view .k-form-controls-button {
	font-size: var(--text-xs);
	--button-rounded: 3px;
	--icon-size: 1rem;
}
</style>
