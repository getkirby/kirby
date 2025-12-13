<template>
	<div class="k-preview-form">
		<header class="k-preview-form-header">
			<k-model-tabs
				:tab="tab.name"
				:tabs="tabsLinkingToPreview"
				class="k-drawer-tabs"
			/>
		</header>

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
</template>

<script>
import { clone } from "@/helpers/object.js";

export default {
	props: {
		api: String,
		blueprint: String,
		content: Object,
		tab: Object,
		tabs: Array,
		lock: Boolean
	},
	computed: {
		tabsLinkingToPreview() {
			const tabs = clone(this.tabs);

			for (const tab in tabs) {
				delete tabs[tab].link;
				tabs[tab].click = (e) => {
					e?.preventDefault();
					this.$panel.view.reload({ query: { tab: tabs[tab].name } });
				};
			}

			return tabs;
		}
	},
	mounted() {
		this.$events.on("section.loaded", this.fixLinksInSection);
	},
	unmounted() {
		this.$events.off("section.loaded", this.fixLinksInSection);
	},
	methods: {
		fixLinksInSection(section) {
			for (const link of section.$el.querySelectorAll(
				".k-item-title > .k-link"
			)) {
				link.__vue__.onClick = (e) => {
					const url = link.__vue__.to;

					if (url.match(/^\/pages\/[^\/]+$/)) {
						e.preventDefault();
						this.$panel.view.open(url + "/preview/form");
					}
				};
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
}
.k-preview-form-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	background: var(--color-gray-150);
	height: var(--input-height);
	border-bottom: 1px solid var(--color-border);
}

.k-preview-form-header .k-tabs {
	flex-grow: 1;
	margin-bottom: 0;
	justify-content: start;
}
.k-preview-form > .k-sections {
	padding: var(--spacing-6) var(--spacing-3) var(--spacing-12);
	overflow-y: auto;
	height: calc(100% - var(--input-height));
}
</style>
