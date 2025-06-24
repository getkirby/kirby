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

/**
 * @deprecated 5.0.0 Use `<k-tags-field-preview>` instead
 */
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
	},
	mounted() {
		window.panel.deprecated(
			"<k-bubbles-field-preview> will be removed in a future version. Use <k-tags-field-preview> instead."
		);
	}
};
</script>

<style>
.k-bubbles-field-preview {
	--bubble-back: var(--panel-color-back);
	--bubble-text: var(--color-text);

	padding: 0.375rem var(--table-cell-padding);
	overflow: hidden;
}
.k-bubbles-field-preview .k-bubbles {
	gap: 0.375rem;
}
</style>
