<template>
	<div class="k-lab-docs">
		<k-lab-docs-warning title="Deprecated" :text="deprecated" />
		<k-lab-docs-warning
			v-if="isUnstable"
			icon="lab"
			title="Unstable"
			text="This component has been marked as unstable and may change in the future."
		/>

		<section v-if="since" class="k-lab-docs-view-since">
			Since <k-tag :text="since" theme="light" />
		</section>

		<k-lab-docs-description :description="description" />
		<k-lab-docs-examples :examples="examples" />
		<k-lab-docs-props :props="props" />
		<k-lab-docs-slots :slots="slots" />
		<k-lab-docs-events :events="events" />
		<k-lab-docs-methods :methods="methods" />
		<k-lab-docs-docblock :doc-block="docBlock" />
	</div>
</template>

<script>
import Warning from "./Docs/Warning.vue";
import Desc, { props as DescProps } from "./Docs/Description.vue";
import Examples, { props as ExamplesProps } from "./Docs/Examples.vue";
import Props, { props as PropsProps } from "./Docs/Props.vue";
import Slots, { props as SlotsProps } from "./Docs/Slots.vue";
import Events, { props as EventsProps } from "./Docs/Events.vue";
import Methods, { props as MethodsProps } from "./Docs/Methods.vue";
import DocBlock, { props as DocBlockProps } from "./Docs/DocBlock.vue";

import DocWarning from "./DocsWarning.vue";
import DocParams from "./DocsParams.vue";
import DocTypes from "./DocsTypes.vue";

export default {
	components: {
		"k-lab-docs-warning": Warning,
		"k-lab-docs-description": Desc,
		"k-lab-docs-examples": Examples,
		"k-lab-docs-props": Props,
		"k-lab-docs-slots": Slots,
		"k-lab-docs-events": Events,
		"k-lab-docs-methods": Methods,
		"k-lab-docs-docblock": DocBlock
	},
	mixins: [
		DescProps,
		ExamplesProps,
		PropsProps,
		SlotsProps,
		EventsProps,
		MethodsProps,
		DocBlockProps
	],
	props: {
		component: String,
		deprecated: String,
		isUnstable: Boolean,
		since: String
	},
	created() {
		if (this.$helper.isComponent("k-lab-docs-warning") === false) {
			window.panel.app.component("k-lab-docs-warning", DocWarning);
			window.panel.app.component("k-lab-docs-params", DocParams);
			window.panel.app.component("k-lab-docs-types", DocTypes);
		}
	}
};
</script>

<style>
.k-lab-docs-section + .k-lab-docs-section {
	margin-top: var(--spacing-12);
}
.k-lab-docs-section .k-headline {
	margin-bottom: var(--spacing-3);
}
.k-lab-docs-section .k-table td {
	padding: 0.375rem var(--table-cell-padding);
	vertical-align: top;
	line-height: 1.5;
	word-break: break-word;
}

.k-lab-docs-description :where(.k-text, .k-box) + :where(.k-text, .k-box) {
	margin-top: var(--spacing-3);
}

.k-lab-docs-required {
	margin-inline-start: var(--spacing-1);
	font-size: 0.7rem;
	vertical-align: super;
	color: var(--color-red-600);
}
.k-lab-docs-since {
	margin-top: var(--spacing-1);
	font-size: var(--text-xs);
	color: var(--color-gray-600);
}

.k-lab-docs-view-since {
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
	margin-bottom: var(--spacing-8);
}

.k-lab-docs-view-since .k-tag {
	--tag-color-back: var(--color-yellow-400);
}
</style>
