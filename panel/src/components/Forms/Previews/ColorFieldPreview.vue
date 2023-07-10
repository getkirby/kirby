<template>
	<div class="k-color-field-preview">
		<k-bubble :text="text">
			<template #image>
				<span
					:style="'color: ' + value"
					class="k-item-figure k-color-preview"
				/>
			</template>
		</k-bubble>
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
	padding: 0.325rem 0.75rem;
}
/** TODO: .k-color-field-preview .k-color-preview:has(+ .k-bubble-text) */
.k-color-field-preview .k-color-preview[data-has-text] {
	border-start-end-radius: 0;
	border-end-end-radius: 0;
}
</style>
