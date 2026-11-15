<template>
	<div class="k-bubbles-field-preview" :class="$options.class">
		<k-bubbles :bubbles="bubbles" :html="html" />
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";
import { props as BubblesProps } from "@/components/Layout/Bubbles.vue";

export default {
	mixins: [FieldPreview, BubblesProps],
	props: {
		value: {
			default: () => [],
			type: [Array, String]
		}
	},
	computed: {
		bubbles() {
			let bubbles = this.value;

			// predefined options
			const options = this.column.options ?? this.field.options;

			// a value without option is only a custom tag
			// if the field accepts any value
			const custom = options === undefined || this.field.accept === "all";

			if (typeof bubbles === "string") {
				bubbles = bubbles.split(",").map((bubble) => bubble.trim());
			}

			return (bubbles ?? []).map((bubble) => {
				if (typeof bubble === "string") {
					bubble = {
						value: bubble,
						text: bubble
					};
				}

				const option = options?.find((option) => option.value === bubble.value);

				if (option) {
					bubble.text = option.text;
				} else if (custom === false) {
					bubble.text = `[${this.$t("unknown")}]`;
				}

				return bubble;
			});
		}
	}
};
</script>

<style>
.k-bubbles-field-preview {
	--bubble-back: var(--color-light);
	--bubble-text: var(--color-black);

	padding: 0.375rem var(--table-cell-padding);
	overflow: hidden;
}
.k-bubbles-field-preview .k-bubbles {
	gap: 0.375rem;
}
</style>
