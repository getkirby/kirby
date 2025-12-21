<template>
	<k-panel class="k-panel-inside k-preview-view" :data-mode="mode">
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
				v-if="!isRemote && mode !== 'compare'"
				:current="size"
				:sizes="sizes"
				@change="onSize"
			/>

			<k-view-buttons :buttons="buttons" />
		</header>

		<main
			ref="grid"
			class="k-preview-view-grid"
			:style="`--preview-width: ${sizes[size].width}`"
		>
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
				large: { icon: "display", width: "100%" }
			})
		},
		src: Object
	},
	data() {
		const size = localStorage.getItem("kirby$preview$size");

		return {
			channel: null,
			isPinned: false,
			size: size && this.sizes[size] ? size : Object.keys(this.sizes).pop()
		};
	},
	computed: {
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
			this.$refs.grid.classList.add("is-animating");
			this.size = size;
			setTimeout(() => this.$refs.grid.classList.remove("is-animating"), 150);
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
		 */
		reload() {
			this.onTreeNavigate({ id: this.id }, true);
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
		return {
			isRemote: false
		};
	},
	watch: {
		mode() {
			this.announce("remote:exit");
		}
	},
	mounted() {
		this.onRemoteAtLaunch();

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
					this.isRemote = false;
					this.channel.close();
					this.channel = null;
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
		/**
		 * Check if remote=true is set as query and if, try to automatically open
		 * remote preview window and remove query from current URL
		 */
		onRemoteAtLaunch() {
			const query = new URLSearchParams(window.location.search);

			if (query.get("remote") === "true") {
				window.open(this.$panel.view.path + "/remote");
				this.onRemote();
				window.history.replaceState(
					{},
					"",
					window.location.protocol +
						"//" +
						window.location.host +
						window.location.pathname
				);
			}
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
	display: grid;
	grid-template-columns: 1fr;
	padding: 0 var(--spacing-3) var(--spacing-3);
	gap: var(--spacing-3);
	max-height: calc(100vh - 3.5rem);
}
.k-preview-view-grid.is-animating {
	transition: grid-template-columns 0.12s ease;
}

@media screen and (max-width: 60rem) {
	.k-preview-view:where([data-mode="compare"], [data-mode="form"])
		.k-preview-view-grid {
		grid-template-rows: 1fr 1fr;
	}

	.k-preview-view-sizes {
		display: none;
	}
}

@media screen and (min-width: 60rem) {
	.k-preview-view .k-preview-view-grid:has(.k-preview-browser) {
		grid-template-columns: calc(var(--preview-width) + 2px);
		justify-content: center;
	}

	.k-preview-view[data-mode="compare"] .k-preview-view-grid {
		grid-template-columns: 1fr 1fr;
	}

	.k-preview-view[data-mode="form"]:has(.k-preview-browser)
		.k-preview-view-grid {
		grid-template-columns: min(calc(var(--preview-width) + 2px), calc(68vw)) 1fr;
	}
}

/** Shared styling for form controls inside preview form and preview browser */
.k-preview-view .k-form-controls-button {
	font-size: var(--text-xs);
	--button-rounded: 3px;
	--icon-size: 1rem;
}
</style>
