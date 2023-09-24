<template>
	<div :data-has-text="Boolean(text)" class="k-color-field-preview">
		<k-tag>
			<template #image>
				<k-color-frame :color="value" />
			</template>
			<template v-if="text">
				{{ text }}
			</template>
		</k-tag>
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		field: Object,
		value: String
	},
	computed: {
		text() {
			const option = this.field.options.find(
				(option) =>
					this.$library.colors.toString(
						option.value,
						this.field.format,
						this.field.alpha
					) === this.value
			);

			if (option) {
				return option.text;
			}

			return null;
		}
	}
};
</script>

<style>
.k-color-field-preview {
	--tag-height: var(--height-sm);
	--tag-color-back: var(--color-gray-200);
	--tag-color-text: var(--color-black);
	--tag-color-focus-back: var(--tag-color-back);
	--tag-color-focus-text: var(--tag-color-text);
	--tag-rounded: var(--rounded);

	--color-frame-rounded: var(--tag-rounded);
	--color-frame-size: var(--tag-height);

	padding: 0.325rem 0.75rem;
}
/** TODO: .k-color-field-preview .k-color-preview:has(+ .k-bubble-text) */
.k-color-field-preview[data-has-text="true"]
	:where(.k-color-frame, .k-color-frame::after) {
	border-start-end-radius: 0;
	border-end-end-radius: 0;
}
</style>
