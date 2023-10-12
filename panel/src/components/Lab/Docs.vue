<template>
	<div class="k-lab-docs">
		<section v-if="description.length" class="k-lab-docs-section">
			<k-headline class="h3">Description</k-headline>
			<k-box theme="text">
				<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
				<k-text v-html="description" />
			</k-box>
		</section>

		<k-lab-docs-examples :examples="examples" />

		<k-lab-docs-props :props="props" />
		<k-lab-docs-slots :slots="slots" />
		<k-lab-docs-events :events="events" />
		<k-lab-docs-methods :methods="methods" />
	</div>
</template>

<script>
import DocsExamples, { props as DocsExamplesProps } from "./DocsExamples.vue";
import DocsProps, { props as DocsPropsProps } from "./DocsProps.vue";
import DocsSlots, { props as DocsSlotsProps } from "./DocsSlots.vue";
import DocsEvents, { props as DocsEventsProps } from "./DocsEvents.vue";
import DocsMethods, { props as DocsMethodsProps } from "./DocsMethods.vue";

export default {
	components: {
		"k-lab-docs-examples": DocsExamples,
		"k-lab-docs-props": DocsProps,
		"k-lab-docs-slots": DocsSlots,
		"k-lab-docs-events": DocsEvents,
		"k-lab-docs-methods": DocsMethods
	},
	mixins: [
		DocsExamplesProps,
		DocsPropsProps,
		DocsSlotsProps,
		DocsEventsProps,
		DocsMethodsProps
	],
	props: {
		component: String,
		description: String
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
.k-lab-docs-types {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}

.k-lab-docs-description :where(.k-text, .k-box) + :where(.k-text, .k-box) {
	margin-top: var(--spacing-3);
}

.k-lab-docs-deprecated {
	--box-height: var(--height-xs);
	font-size: var(--text-xs);
}

.k-lab-docs-values {
	font-size: var(--text-xs);
	border-left: 2px solid var(--color-blue-300);
	padding-inline-start: var(--spacing-2);
}
.k-lab-docs-values span {
	display: inline-flex;
	flex-wrap: wrap;
	gap: var(--spacing-1);
}

.k-lab-docs-types code[data-type="boolean"] {
	color: var(--color-purple-800);
	outline-color: var(--color-purple-400);
	background: var(--color-purple-300);
}
.k-lab-docs-types code[data-type="string"] {
	color: var(--color-green-800);
	outline-color: var(--color-green-500);
	background: var(--color-green-300);
}
.k-lab-docs-types code[data-type="number"] {
	color: var(--color-orange-800);
	outline-color: var(--color-orange-500);
	background: var(--color-orange-300);
}
.k-lab-docs-types code[data-type="array"] {
	color: var(--color-aqua-800);
	outline-color: var(--color-aqua-500);
	background: var(--color-aqua-300);
}
.k-lab-docs-types code[data-type="object"] {
	color: var(--color-yellow-800);
	outline-color: var(--color-yellow-500);
	background: var(--color-yellow-300);
}
.k-lab-docs-types code[data-type="func"] {
	color: var(--color-pink-800);
	outline-color: var(--color-pink-400);
	background: var(--color-pink-300);
}
</style>
