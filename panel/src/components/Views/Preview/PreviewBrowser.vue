<template>
	<div class="k-preview-browser">
		<header v-if="label" class="k-preview-browser-header">
			<k-headline class="k-preview-headline">
				<k-icon :type="isLoading ? 'loader' : 'git-branch'" />
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

		<div class="k-preview-browser-frame">
			<iframe
				ref="browserA"
				:class="['k-preview-browser-iframe', active === 0 ? 'is-active' : null]"
				:src="srcs[0]"
				@load="onLoad(0)"
			/>
			<iframe
				ref="browserB"
				:class="['k-preview-browser-iframe', active === 1 ? 'is-active' : null]"
				:src="srcs[1]"
				@load="onLoad(1)"
			/>
		</div>
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
	data() {
		return {
			isLoading: false,
			active: 0,
			pending: null,
			pendingScroll: null,
			srcs: [this.src, null]
		};
	},
	computed: {
		window() {
			return this.activeIframe()?.contentWindow;
		}
	},
	watch: {
		src(value) {
			this.loadSrc(value);
		}
	},
	methods: {
		/**
		 * Returns the currently visible iframe element
		 */
		activeIframe() {
			return this.getIframe(this.active);
		},
		/**
		 * Adds a cache-busting param to force an iframe reload
		 */
		addReloadParam(src) {
			const url = new URL(src, window.location.origin);
			url.searchParams.set("_reload", Date.now());
			return url.toString();
		},
		/**
		 * Returns iframe element by index (0 = A, 1 = B)
		 */
		getIframe(index) {
			return this.$refs[index === 0 ? "browserA" : "browserB"];
		},
		/**
		 * Double-buffered load:
		 * - set src on the inactive iframe
		 * - swap to it once the load finishes
		 */
		loadSrc(src, { force = false } = {}) {
			this.pending = this.active === 0 ? 1 : 0;
			const iframe = this.getIframe(this.pending);
			this.isLoading = true;

			this.srcs[this.pending] = force ? this.addReloadParam(src) : src;

			// If the target iframe already has the src loaded, swap immediately
			if (
				iframe?.src === this.srcs[this.pending] &&
				iframe.contentDocument?.readyState === "complete"
			) {
				this.active = this.pending;
				this.pending = null;
				this.isLoading = false;
			}
		},
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
		/**
		 * Handles iframe load events for both buffers
		 */
		async onLoad(index) {
			const iframe = this.getIframe(index);
			const document = iframe?.contentDocument;

			if (!document) {
				return;
			}

			if (this.pending === index) {
				// Only swap when the preloaded iframe finishes loading
				this.pending = null;
				this.active = index;
				this.isLoading = false;

				if (this.pendingScroll !== null) {
					const scrollY = this.pendingScroll;
					this.pendingScroll = null;

					await this.$nextTick();
					iframe.contentWindow.scrollTo(0, scrollY);
				}
			}

			if (index !== this.active) {
				return;
			}

			// if the browser got redirected during load
			// navigate to the proper preview URL for this new URL
			// (but only if the new URL doesn't already contain _version and _token)
			if (this.srcs[index] !== document.URL) {
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
		 * Refresh the iframe (e.g. for content updates)
		 */
		reload() {
			// keep scroll in place when swapping buffers
			this.pendingScroll = this.window?.scrollY ?? 0;
			this.loadSrc(this.src, { force: true });
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
			const target = this.active === 0 ? 1 : 0;
			const iframe = this.getIframe(target);

			iframe?.addEventListener(
				"load",
				() => iframe.contentWindow.scrollTo(0, scroll),
				{ once: true }
			);

			// load restored URL in iframe
			this.srcs[target] = src;
			this.pending = target;
			this.isLoading = true;
		},
		/**
		 * Returns the current iframe URL and scroll position,
		 * so that these can be restored, if needed
		 */
		store() {
			return {
				src: this.activeIframe()?.src,
				scroll: this.window?.scrollY
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
	position: relative;
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
.k-preview-browser-frame {
	width: 100%;
	flex-grow: 1;
	position: relative;
}
.k-preview-browser-iframe {
	position: absolute;
	inset: 0;
	width: 100%;
	height: 100%;
	border: 0;
	opacity: 0;
	pointer-events: none;
}
.k-preview-browser-iframe.is-active {
	opacity: 1;
	pointer-events: auto;
}
@container (max-width: 30rem) {
	.k-preview-browser-message {
		display: none;
	}
}
</style>
