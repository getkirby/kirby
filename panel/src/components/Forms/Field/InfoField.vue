<template>
	<div class="k-field k-info-field">
		<k-headline>{{ label }}</k-headline>
		<k-box :theme="theme">
			<k-text :html="text" />
		</k-box>
		<footer v-if="help" class="k-field-footer">
			<k-text v-if="help" theme="help" class="k-field-help" :html="help" />
		</footer>
	</div>
</template>

<script>
import { help, label } from "@/mixins/props.js";

/**
 * @example <k-info-field label="Info" text="This is a nice info text" />
 */
export default {
	mixins: [help, label],
	props: {
		/**
		 * Sets the info text. You can use HTML here to format the info.
		 */
		text: String,
		/**
		 * The info box can have different styles/themes.
		 * @values The themes are inherited from the `<k-box>` component.
		 */
		theme: {
			type: String,
			default: "info"
		}
	}
};
</script>

<style>
.k-info-field .k-headline {
	padding-bottom: 0.75rem;
	line-height: 1.25rem;
}
</style>
