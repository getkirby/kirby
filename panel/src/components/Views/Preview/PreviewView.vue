<template>
	<k-panel class="k-panel-inside k-preview-view">
		<header class="k-preview-view-header">
			<k-button-group>
				<k-button
					:responsive="true"
					:title="$t('back')"
					icon="angle-left"
					size="sm"
					variant="filled"
					@click="exit"
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

			<k-preview-sizes
				v-if="!isRemote"
				:current="size"
				:mode="mode"
				:sizes="sizes"
				@change="onSize"
			/>

			<k-button-group>
				<k-button
					v-if="mode === 'compare'"
					:aria-checked="isScrollSyncing"
					:theme="isScrollSyncing ? 'info-icon' : 'passive'"
					:title="$t('preview.browser.scroll')"
					icon="scroll-to-bottom"
					role="switch"
					size="sm"
					variant="filled"
					class="k-preview-scroll-sync"
					@click="onScrollSyncing"
				/>

				<k-view-buttons :buttons="buttons" />
			</k-button-group>
		</header>

		<main :data-mode="mode" :style="gridStyles" class="k-preview-view-grid">
			<template v-if="mode === 'form'">
				<k-preview-browser
					v-if="!isRemote"
					ref="browser"
					v-bind="browserProps('form')"
					:open="$panel.view.path + '/remote'"
					@navigate="onBrowserNavigate"
					@open="onRemote"
					@pin="togglePin"
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
					ref="latest"
					v-bind="browserProps('latest')"
					@discard="onDiscard"
					@navigate="onBrowserNavigate"
					@scroll="onScroll('latest', 'changes')"
					@submit="onSubmit"
				/>
				<k-preview-browser
					ref="changes"
					v-bind="browserProps('changes')"
					@discard="onDiscard"
					@navigate="onBrowserNavigate"
					@scroll="onScroll('changes', 'latest')"
					@submit="onSubmit"
				/>
			</template>

			<k-preview-browser
				v-else
				v-bind="browserProps(mode)"
				@discard="onDiscard"
				@navigate="onBrowserNavigate"
				@submit="onSubmit"
			/>
		</main>

		<k-panel-notifications />
	</k-panel>
</template>

<script>
import ModelView from "@/components/Views/ModelView.vue";

export const Preview = {
	inheritAttrs: false,
	props: {
		id: String,
		mode: String,
		sizes: {
			type: Object,
			default: () => ({
				small: { icon: "mobile", width: "390px" },
				medium: { icon: "tablet", width: "820px" },
				large: { icon: "display", width: "1440px" }
			})
		},
		src: Object
	},
	data() {
		const size = localStorage.getItem("kirby$preview$size");

		return {
			channel: null,
			isAnimating: false,
			isPinned: false,
			size: size && this.sizes[size] ? size : Object.keys(this.sizes).pop()
		};
	},
	computed: {
		gridStyles() {
			return {
				"--size": this.sizes[this.size].width,
				[this.isAnimating ? "transition" : null]:
					"grid-template-columns 0.12s ease"
			};
		}
	},
	mounted() {
		this.$events.on("keydown.esc", this.exit);
	},
	unmounted() {
		this.$events.off("keydown.esc", this.exit);
	},
	methods: {
		/**
		 * Send message across tabs
		 */
		announce(action, data) {
			this.channel?.postMessage({ on: action, ...data });
		},
		/**
		 * Reload the browser to reflect updated content
		 */
		onChanges() {
			this.$refs.browser?.reload();
		},
		/**
		 * Reloads the view or just the browser src
		 * triggered by navigation in the browser
		 */
		onBrowserNavigate({ browser = null, view = null }) {
			this.$panel.view.reload({ query: { browser, view } });
		},
		/**
		 * Sets a view size and remembers it in localStorage
		 */
		async onSize(size) {
			this.isAnimating = true;
			this.size = size;
			setTimeout(() => (this.isAnimating = false), 150);
			localStorage.setItem("kirby$preview$size", size);
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
		 * Reloads the preview view for the current ID
		 * but preserves the tab selection
		 */
		reload() {
			let url = this.$panel.view.path;
			const query = new URLSearchParams(window.location.search);

			if (query.has("tab") === true) {
				url += "?tab=" + query.get("tab");
			}

			this.$panel.view.open(url);
		},
		togglePin() {
			this.isPinned = !this.isPinned;

			// when unpinning, reset the iframe to match the preview view
			if (!this.isPinned) {
				this.reload();
			}
		}
	}
};

export default {
	extends: ModelView,
	mixins: [Preview],
	props: {
		back: String,
		title: String
	},
	data() {
		const scroll = localStorage.getItem("kirby$preview$scroll", "true");

		return {
			isRemote: false,
			isScrollSyncing: scroll === "true"
		};
	},
	watch: {
		mode() {
			this.announce("remote:exit");
		}
	},
	mounted() {
		this.$events.on("content.save", this.onChanges);
		this.$events.on("content.discard", this.onChanges);
		this.$events.on("content.publish", this.onChanges);
		this.$events.on("model.update", this.onChanges);
		this.$events.on("page.changeTitle", this.onChanges);
		this.$events.on("page.sort", this.onChanges);
		this.$events.on("file.sort", this.onChanges);
	},
	unmounted() {
		this.$events.off("content.save", this.onChanges);
		this.$events.off("content.discard", this.onChanges);
		this.$events.off("content.publish", this.onChanges);
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
				isPinned: this.isPinned,
				isProcessing: this.isSaving,
				label: this.$t("version." + src),
				modified: this.modified,
				open: this.src[src],
				src: this.src[src],
				mode
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

			this.announce("remote:exit");
			this.$panel.view.open(this.link);
		},
		onChanges() {
			Preview.methods.onChanges.call(this);
			this.announce("host:changes");
		},
		onRemote() {
			this.isRemote = true;
			this.channel = new BroadcastChannel("preview$" + this.id);

			this.channel.addEventListener("message", (event) => {
				if (event.data.on === "remote:exit") {
					this.channel.close();
					this.channel = null;
					this.isRemote = false;
					return;
				}
				if (event.data.on === "remote:browser") {
					return this.onBrowserNavigate(event.data);
				}
				if (event.data.on === "remote:reload") {
					return this.reload();
				}
			});

			window.addEventListener("beforeunload", () =>
				this.announce("remote:exit")
			);
		},
		onScroll(source, target) {
			if (this.isScrollSyncing) {
				const scrollY = this.$refs[source]?.window?.scrollY ?? 0;
				this.$refs[target]?.scrollTo(scrollY);
			}
		},
		onScrollSyncing() {
			this.isScrollSyncing = !this.isScrollSyncing;
			localStorage.setItem("kirby$preview$scroll", this.isScrollSyncing);
		},
		async onViewNavigate(url) {
			Preview.methods.onViewNavigate.call(this, url);
			this.announce("host:view", { url });
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
	display: grid;
	grid-template-columns: 1fr auto 1fr;
	gap: var(--spacing-2);
	align-items: center;
	padding: var(--spacing-3);
}
.k-preview-view-header > * {
	justify-self: center;
}
.k-preview-view-header > :first-child {
	justify-self: start;
}
.k-preview-view-header > :last-child {
	justify-self: end;
}
.k-preview-headline {
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
	font-weight: var(--font-normal);
	padding-inline: var(--spacing-1);
}
.k-preview-view-tree {
	--tree-branch-color-back: transparent;
	--tree-branch-hover-color-back: var(--color-gray-800);
	--tree-branch-selected-color-back: var(--color-blue-800);

	width: 20rem;
}

.k-preview-view-grid {
	display: grid;
	grid-template-columns: 1fr;
	padding: 0 var(--spacing-3) var(--spacing-3);
	gap: var(--spacing-3);
	max-height: calc(100vh - 3.5rem);

	--preview-width: calc(var(--size) + 2px);
}

@media screen and (max-width: 60rem) {
	.k-preview-view-grid:where(
		[data-mode="compare"],
		[data-mode="form"]:has(.k-preview-browser)
	) {
		grid-template-rows: 1fr 1fr;
	}

	.k-preview-view-header {
		grid-template-columns: auto auto;
	}

	.k-preview-sizes {
		display: none;
	}
}

@media screen and (min-width: 60rem) {
	.k-preview-view-grid:has(.k-preview-browser) {
		grid-template-columns: min(var(--preview-width), 100%);
		justify-content: space-around;
	}

	.k-preview-view-grid[data-mode="compare"] {
		grid-template-columns:
			min(var(--preview-width), calc(50% - var(--spacing-3) / 2))
			min(var(--preview-width), calc(50% - var(--spacing-3) / 2));
	}

	.k-preview-view-grid[data-mode="form"]:has(.k-preview-browser) {
		grid-template-columns: min(var(--preview-width), 68%) 1fr;
	}
}

/** Shared styling for form controls inside preview form and preview browser */
.k-preview-view .k-form-controls-button {
	font-size: var(--text-xs);
	--button-rounded: 3px;
	--icon-size: 1rem;
}
</style>
