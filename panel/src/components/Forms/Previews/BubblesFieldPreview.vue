<template>
	<div
		:class="['k-bubbles-field-preview', $options.class, $attrs.class]"
		:style="$attrs.style"
	>
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
			const options = this.column.options ?? this.field.options ?? [];

			if (typeof bubbles === "string") {
				bubbles = bubbles.split(",");
			}

			return (bubbles ?? []).map((bubble) => {
				if (typeof bubble === "string") {
					bubble = {
						value: bubble,
						text: bubble
					};
				}

				for (const option of options) {
					if (option.value === bubble.value) {
						bubble.text = option.text;
					}
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
