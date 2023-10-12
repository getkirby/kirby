<template>
	<div class="k-lab-docs">
		<k-lab-docs-description :description="description" />
		<k-lab-docs-examples :examples="examples" />
		<k-lab-docs-props :props="props" />
		<k-lab-docs-slots :slots="slots" />
		<k-lab-docs-events :events="events" />
		<k-lab-docs-methods :methods="methods" />
	</div>
</template>

<script>
import Desc, { props as DescProps } from "./Docs/Description.vue";
import Examples, { props as ExamplesProps } from "./Docs/Examples.vue";
import Props, { props as PropsProps } from "./Docs/Props.vue";
import Slots, { props as SlotsProps } from "./Docs/Slots.vue";
import Events, { props as EventsProps } from "./Docs/Events.vue";
import Methods, { props as MethodsProps } from "./Docs/Methods.vue";

export default {
	components: {
		"k-lab-docs-description": Desc,
		"k-lab-docs-examples": Examples,
		"k-lab-docs-props": Props,
		"k-lab-docs-slots": Slots,
		"k-lab-docs-events": Events,
		"k-lab-docs-methods": Methods
	},
	mixins: [
		DescProps,
		ExamplesProps,
		PropsProps,
		SlotsProps,
		EventsProps,
		MethodsProps
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
