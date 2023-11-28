<template>
	<div class="k-lab-docs">
		<k-lab-docs-deprecated :deprecated="deprecated" />
		<k-lab-docs-description :description="description" :since="since" />
		<k-lab-docs-examples :examples="examples" />
		<k-lab-docs-props :props="props" />
		<k-lab-docs-slots :slots="slots" />
		<k-lab-docs-events :events="events" />
		<k-lab-docs-methods :methods="methods" />
		<k-lab-docs-docblock :doc-block="docBlock" />
	</div>
</template>

<script>
import Vue from "vue";

import Deprecated, { props as DeprecatedProps } from "./Docs/Deprecated.vue";
import Desc, { props as DescProps } from "./Docs/Description.vue";
import Examples, { props as ExamplesProps } from "./Docs/Examples.vue";
import Props, { props as PropsProps } from "./Docs/Props.vue";
import Slots, { props as SlotsProps } from "./Docs/Slots.vue";
import Events, { props as EventsProps } from "./Docs/Events.vue";
import Methods, { props as MethodsProps } from "./Docs/Methods.vue";
import DocBlock, { props as DocBlockProps } from "./Docs/DocBlock.vue";

import DocDeprecated from "./DocsDeprecated.vue";
import DocParams from "./DocsParams.vue";
import DocTypes from "./DocsTypes.vue";
Vue.component("k-lab-docs-deprecated", DocDeprecated);
Vue.component("k-lab-docs-params", DocParams);
Vue.component("k-lab-docs-types", DocTypes);

export default {
	components: {
		"k-lab-docs-deprecated": Deprecated,
		"k-lab-docs-description": Desc,
		"k-lab-docs-examples": Examples,
		"k-lab-docs-props": Props,
		"k-lab-docs-slots": Slots,
		"k-lab-docs-events": Events,
		"k-lab-docs-methods": Methods,
		"k-lab-docs-docblock": DocBlock
	},
	mixins: [
		DeprecatedProps,
		DescProps,
		ExamplesProps,
		PropsProps,
		SlotsProps,
		EventsProps,
		MethodsProps,
		DocBlockProps
	],
	props: {
		component: String
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
</style>
