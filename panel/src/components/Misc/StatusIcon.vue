<template>
	<k-button
		:disabled="disabled"
		:icon="icon"
		:responsive="responsive"
		:text="text"
		:theme="theme"
		:title="titleAttr"
		:size="size"
		:variant="variant"
		:class="'k-status-icon k-status-icon-' + status"
		@click="onClick"
	/>
</template>

<script>
/**
 * Page status icon
 */
export default {
	props: {
		click: {
			type: Function,
			default: () => {}
		},
		disabled: Boolean,
		responsive: Boolean,
		status: String,
		text: String,
		/**
		 * @deprecated 4.0 Use the `title` prop instead
		 * @todo button.prop.tooltip.deprecated - remove @ 5.0
		 */
		tooltip: String,
		title: String,
		variant: String,
		size: String
	},
	computed: {
		icon() {
			if (this.status === "draft") {
				return "circle-outline";
			}

			if (this.status === "unlisted") {
				return "circle-half";
			}

			return "circle";
		},
		theme() {
			if (this.disabled) {
				return "passive";
			}

			if (this.status === "draft") {
				return "negative";
			}

			if (this.status === "unlisted") {
				return "info";
			}

			return "positive";
		},
		/**
		 * @todo button.prop.tooltip.deprecated - adapt @ 5.0
		 */
		titleAttr() {
			let title = this.title || this.tooltip || this.text;

			if (this.disabled) {
				title += ` (${this.$t("disabled")})`;
			}

			return title;
		}
	},
	methods: {
		onClick() {
			this.click();
			this.$emit("click");
		}
	}
};
</script>

<style>
.k-button.k-status-icon[data-variant="filled"] {
	--button-color-icon: var(--theme-color-600);
	--button-color-back: hsla(0, 0%, 0%, 7%);
	--button-color-hover: hsla(0, 0%, 0%, 12%);
}
</style>
