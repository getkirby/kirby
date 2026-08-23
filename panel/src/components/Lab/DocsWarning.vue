<template>
	<k-box
		v-if="text?.length"
		:icon="icon"
		theme="warning"
		class="k-lab-docs-warning"
	>
		<k-text :text="content" />
	</k-box>
</template>

<script>
import html from "@/panel/html";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	props: {
		icon: {
			type: String,
			default: "protected"
		},
		title: String,
		text: String
	},
	computed: {
		content() {
			// both parts were already rendered raw before and the
			// text is kirbytext output, so they stay trusted
			return html("<strong>" + this.title + ":</strong> " + this.text);
		}
	}
};
</script>

<style>
.k-table .k-lab-docs-warning {
	--box-height: var(--height-xs);
	--text-font-size: var(--text-xs);
}
</style>
