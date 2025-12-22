<template>
	<div class="k-preview-browser">
		<header v-if="label" class="k-preview-browser-header">
			<k-headline class="k-preview-headline">
				<k-icon type="git-branch" />
				{{ label }}
			</k-headline>

			<k-button-group>
				<template v-if="mode === 'changes'">
					<p v-if="hasDiff === false" class="k-preview-browser-message">
						{{ $t("lock.unsaved.empty") }}
					</p>
					<k-form-controls
						v-else
						:editor="editor"
						:has-diff="hasDiff"
						:is-locked="isLocked"
						:is-processing="isProcessing"
						:modified="modified"
						size="xs"
						@discard="$emit('discard', $event)"
						@submit="$emit('submit', $event)"
					/>
				</template>

				<k-button
					v-if="mode === 'form'"
					:aria-checked="isPinned"
					:title="$t('preview.browser.pin')"
					:theme="isPinned ? 'info' : 'passive'"
					:variant="isPinned ? 'filled' : 'none'"
					icon="pushpin"
					role="switch"
					size="sm"
					@click="$emit('pin')"
				/>
				<k-button
					v-if="open"
					:link="open"
					icon="open"
					size="xs"
					target="_blank"
					@click="$emit('open')"
				/>
			</k-button-group>
		</header>

		<iframe ref="browser" :src="src" @load="onLoad" />
	</div>
</template>

<script>
import { props } from "@/components/Forms/FormControls.vue";

export default {
	mixins: [props],
	props: {
		isPinned: Boolean,
		label: String,
		src: String,
		mode: String,
		open: String
	},
	emits: ["discard", "navigate", "open", "pin", "scroll", "submit"],
	computed: {
		window() {
			return this.$refs.browser.contentWindow;
		}
	},
	methods: {
		/**
		 * Handle link clicks inside the iframe
		 */
		onClick(e) {
			const link = e.target.closest("a");

			if (!link) {
				return;
			}

			if (!link.href || link.onclick) {
				return;
			}

			// open external links and Panel links in new tab
			if (
				link.href.startsWith(location.origin) === false ||
				link.href.startsWith(this.$panel.urls.panel) === true
			) {
				link.target = "_blank";
				return true;
			}

			// catch internal links and emit navigate event
			e.preventDefault(e);

			if (this.isPinned) {
				// we only want to refresh the browser for the target
				this.$emit("navigate", { browser: link.href });
			} else {
				// we want to refresh the whole view for the target
				this.$emit("navigate", { view: link.href });
			}
		},
		onLoad() {
			const document = this.$refs.browser.contentDocument;

			// if the browser got redirected during load
			// navigate to the proper preview URL for this new URL
			// (but only if the new URL doesn't already contain _version and _token)
			if (this.src !== document.URL) {
				const url = new URL(document.URL);

				if (
					url.searchParams.has("_token") === false ||
					url.searchParams.has("_version") === false
				) {
					return this.$emit("navigate", { browser: url });
				}
			}

			// attach event listeners to all links inside the iframe
			document.addEventListener("click", this.onClick);

			for (const link of document.querySelectorAll("a")) {
				link.addEventListener("click", this.onClick);
			}

			document.addEventListener("scroll", (e) => this.$emit("scroll", e));
		},
		/**
		 * Refresh the iframe
		 * (e.g. for content updates)
		 */
		reload() {
			this.window.location.reload();
		},
		/**
		 * Restore an iframe URL and scroll position
		 * (only when iframe browser is pinned)
		 */
		restore({ src, scroll }) {
			// if the browser isn't pinned, we keep it as loaded with the view
			if (!this.isPinned) {
				return;
			}

			// restore scroll position once the iframe finished loading
			this.$refs.browser.addEventListener(
				"load",
				() => this.window.scrollTo(0, scroll),
				{ once: true }
			);

			// load restored URL in iframe
			this.$refs.browser.src = src;
		},
		/**
		 * Returns the current iframe URL and scroll position,
		 * so that these can be restored, if needed
		 */
		store() {
			return {
				src: this.$refs.browser.src,
				scroll: this.window.scrollY
			};
		}
	}
};
</script>

<style>
:root {
	--preview-browser-color-background: var(--input-color-back);
}
.k-preview-browser {
	container-type: inline-size;
	display: flex;
	flex-direction: column;
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-xl);
	background: var(--preview-browser-color-background);
	overflow: hidden;
	border: 1px solid var(--color-border);
}
.k-preview-browser-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	background: var(--preview-browser-color-background);
	border-bottom: 1px solid var(--color-border);
	color: var(--color-text);
	padding-inline: var(--spacing-2);
	height: var(--input-height);
}
.k-preview-browser-header .k-preview-headline {
	font-size: var(--text-xs);
}
.k-preview-browser-message {
	font-size: var(--text-xs);
	display: flex;
	margin-inline-end: var(--spacing-1);
	color: var(--color-text-dimmed);
}
.k-preview-browser iframe {
	width: 100%;
	flex-grow: 1;
}
@container (max-width: 30rem) {
	.k-preview-browser-message {
		display: none;
	}
}
</style>
