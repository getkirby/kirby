<template>
	<k-panel class="k-panel-inside k-preview-view" :data-version-id="versionId">
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
				<k-dropdown-content ref="tree" theme="dark" class="k-preview-view-tree">
					<k-page-tree :current="id" @click.native.stop @select="navigate" />
				</k-dropdown-content>
			</k-button-group>

			<k-button-group>
				<k-button
					v-if="versionId === 'compare'"
					:aria-checked="isScrollSyncing"
					:icon="isScrollSyncing ? 'scroll-to-bottom-fill' : 'scroll-to-bottom'"
					:theme="isScrollSyncing ? 'info-icon' : 'passive'"
					:title="$t('preview.browser.scroll')"
					role="switch"
					size="sm"
					variant="filled"
					class="k-preview-scroll-sync"
					@click="onScrollSyncing"
				/>

				<k-view-buttons :buttons="buttons" />
			</k-button-group>
		</header>
		<main class="k-preview-view-grid">
			<template v-if="versionId === 'compare'">
				<k-preview-browser
					ref="latest"
					v-bind="browserProps('latest')"
					@discard="onDiscard"
					@scroll="onScroll('latest', 'changes')"
					@submit="onSubmit"
				/>
				<k-preview-browser
					ref="changes"
					v-bind="browserProps('changes')"
					@discard="onDiscard"
					@scroll="onScroll('changes', 'latest')"
					@submit="onSubmit"
				/>
			</template>
			<template v-else>
				<k-preview-browser
					v-bind="browserProps(versionId)"
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
		versionId: String,
		src: Object,
		title: String
	},
	data() {
		const scroll = localStorage.getItem("kirby$preview$scroll", "true");

		return {
			isScrollSyncing: scroll === "true"
		};
	},
	mounted() {
		this.$events.on("keydown.esc", this.exit);
	},
	destroyed() {
		this.$events.off("keydown.esc", this.exit);
	},
	methods: {
		browserProps(versionId) {
			return {
				editor: this.editor,
				hasDiff: this.hasDiff,
				isLocked: this.isLocked,
				modified: this.modified,
				label: this.$t("version." + versionId),
				permissions: this.permissions,
				src: this.src[versionId],
				versionId: versionId
			};
		},
		exit() {
			if (this.$panel.overlays().length > 0) {
				return;
			}

			this.$panel.view.open(this.link);
		},
		navigate(page) {
			if (page.id === this.id) {
				return;
			}

			this.$refs.tree.close();

			if (page.id === "/") {
				return this.$panel.view.open("site/preview/" + this.versionId);
			}

			const url = this.$api.pages.url(page.id, "preview/" + this.versionId);
			this.$panel.view.open(url);
		},
		onScroll(source, target) {
			if (this.isScrollSyncing) {
				this.$refs[target].$refs.browser.contentWindow.scrollTo(
					0,
					this.$refs[source].$refs.browser.contentWindow.scrollY
				);
			}
		},
		onScrollSyncing() {
			this.isScrollSyncing = !this.isScrollSyncing;
			localStorage.setItem("kirby$preview$scroll", this.isScrollSyncing);
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
}
@media screen and (max-width: 60rem) {
	.k-preview-view-grid {
		flex-direction: column;
	}
	.k-preview-view-title {
		display: none;
	}
}
.k-preview-view .k-preview-browser {
	flex-grow: 1;
	flex-basis: 50%;
}
</style>
