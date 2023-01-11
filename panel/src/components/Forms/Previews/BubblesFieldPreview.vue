<template>
	<div class="k-bubbles-field-preview" :class="$options.class">
		<k-bubbles :bubbles="bubbles" />
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		value: [Array, String]
	},
	computed: {
		bubbles() {
			let bubbles = this.value;

			// predefined options
			const options = this.column?.options || this.field?.options || [];

			if (typeof bubbles === "string") {
				bubbles = bubbles.split(",");
			}

			return bubbles.map((bubble) => {
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

				return {
					back: "light",
					color: "black",
					...bubble
				};
			});
		}
	}
};
</script>

<style>
.k-bubbles-field-preview {
	padding: 0.325rem 0.75rem;
}
</style>
