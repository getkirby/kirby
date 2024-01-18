<template>
	<p class="k-url-field-preview" :class="$options.class" @click.stop>
		{{ column.before }}
		<component
			:is="$options.input"
			:value="value"
			@input="$emit('input', $event)"
		/>
		{{ column.after }}
	</p>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	input: "k-url-input",
	props: {
		value: [String, Object]
	}
	// computed: {
	// 	link() {
	// 		return typeof this.value === "object" ? this.value.href : this.value;
	// 	},
	// 	text() {
	// 		if (typeof this.value === "object") {
	// 			return this.value.text;
	// 		}

	// 		return this.link;
	// 	}
	// }
};
</script>

<style>
.k-url-field-preview input {
	text-decoration: underline;
	text-underline-offset: var(--link-underline-offset);
	padding-inline: var(--table-cell-padding);
	height: var(--table-row-height);
	color: var(--link-color);
}
.k-url-field-preview input:focus {
	outline-offset: -2px;
	outline: var(--outline);
}
</style>
