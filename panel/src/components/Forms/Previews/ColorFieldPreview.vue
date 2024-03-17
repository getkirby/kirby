<template>
	<div :class="['k-color-field-preview', $attrs.class]" :style="$attrs.style">
		<k-color-frame :color="value" />
		<template v-if="text">
			{{ text }}
		</template>
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

/**
 * @since 4.0.0
 */
export default {
	mixins: [FieldPreview],
	props: {
		value: String
	},
	computed: {
		text() {
			if (!this.value) {
				return;
			}

			const value = this.$library.colors.toString(
				this.value,
				this.field.format,
				this.field.alpha
			);
			const option = this.field.options?.find(
				(option) =>
					this.$library.colors.toString(
						option.value,
						this.field.format,
						this.field.alpha
					) === value
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
	--color-frame-rounded: var(--tag-rounded);
	--color-frame-size: var(--tag-height);
	padding: 0.375rem var(--table-cell-padding);
	display: flex;
	align-items: center;
	gap: var(--spacing-2);
}
</style>
