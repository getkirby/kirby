<template>
	<div class="k-preview-form">
		<header class="k-preview-form-header">
			<k-model-tabs
				:diff="diff"
				:tab="tab.name"
				:tabs="tabsWithPreviewLinks"
				class="k-drawer-tabs"
			/>

			<k-form-controls
				:editor="editor"
				:has-diff="hasDiff"
				:is-locked="isLocked"
				:is-processing="isProcessing"
				:modified="modified"
				size="xs"
				@discard="$emit('discard', $event)"
				@submit="$emit('submit', $event)"
			/>
		</header>

		<div class="k-preview-form-body">
			<k-sections
				:blueprint="blueprint"
				:content="content"
				:empty="$t('page.blueprint', { blueprint: $esc(blueprint) })"
				:lock="lock"
				:parent="api"
				:tab="tab"
				@input="$emit('input', $event)"
				@submit="$emit('submit', $event)"
			/>
		</div>
	</div>
</template>

<script>
import { props as FormControls } from "@/components/Forms/FormControls.vue";

export default {
	mixins: [FormControls],
	props: {
		api: String,
		blueprint: String,
		content: Object,
		diff: Object,
		tab: Object,
		tabs: Array,
		lock: Boolean
	},
	emits: ["discard", "input", "navigate", "submit"],
	computed: {
		tabsWithPreviewLinks() {
			const query = new URLSearchParams(window.location.search);

			return this.tabs.map((tab) => {
				query.append("tab", tab.name);

				return {
					...tab,
					link: this.$panel.view.path + "?" + query.toString()
				};
			});
		}
	},
	mounted() {
		this.$events.on("section.loaded", this.fixLinksInSection);
	},
	unmounted() {
		this.$events.off("section.loaded", this.fixLinksInSection);
	},
	methods: {
		/**
		 * Overwrites all links to page views in the section
		 * to open the corresponding page preview view instead
		 */
		fixLinksInSection(section) {
			const links = section.$el.querySelectorAll(".k-item-title > .k-link");
			for (const link of links) {
				const url = link.__vue__.to;

				if (url?.match(/^\/pages\/[^/]+$/)) {
					link.__vue__.onClick = (e) => {
						e.preventDefault();
						this.$emit("navigate", url + "/preview/form");
					};
				}
			}
		}
	}
};
</script>

<style>
.k-preview-form {
	border: 1px solid var(--color-border);
	border-radius: var(--rounded-lg);
	overflow: hidden;
	container-type: inline-size;
}
.k-preview-form-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	background: light-dark(var(--color-gray-150), var(--input-color-back));
	height: var(--input-height);
	border-bottom: 1px solid var(--color-border);
}

.k-preview-form-header .k-form-controls {
	margin-inline-start: auto;
}

.k-preview-form-header .k-tabs {
	flex-grow: 1;
	margin-bottom: 0;
	justify-content: start;
}
.k-preview-form-header .k-tabs .k-tabs-button[aria-current="true"]::after {
	bottom: -1px;
}
.k-preview-form-header .k-tabs-button .k-button-badge {
	top: 6px;
	font-size: 0.5rem;
}

.k-preview-form-header .k-form-controls {
	margin-inline-end: var(--button-padding);
}

.k-preview-form-body {
	padding: var(--spacing-6) var(--spacing-6) var(--spacing-12);
	overflow-y: auto;
	height: calc(100% - var(--input-height));
}
</style>
