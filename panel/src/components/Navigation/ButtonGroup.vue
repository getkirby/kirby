<template>
	<div :data-layout="layout" class="k-button-group">
		<slot v-if="$slots.default" />
		<k-button
			v-for="(button, index) in buttons"
			v-else
			:key="index"
			v-bind="{
				variant,
				theme,
				size,
				responsive,
				...button
			}"
		/>
	</div>
</template>

<script>
/**
 * The Button Group should always be used when two or more buttons are positioned next to each other. The Button Group takes care of consistent margins between buttons.
 *
 * @example <k-button-group>
  <k-button icon="edit">Edit</k-button>
  <k-button icon="trash">Delete</k-button>
</k-button-group>
 */
export default {
	props: {
		/**
		 * Either pass the buttons as default slot
		 * or as an array to this prop
		 */
		buttons: Array,
		/**
		 * Styling/layout variations
		 * @values `collapsed`, `dropdown`
		 */
		layout: String,
		/**
		 * Styling variants - see `<k-button>` for details.
		 * Default for buttons if not defined individually.
		 */
		variant: String,
		/**
		 * Color theme - see `<k-button>` for details.
		 * Default for buttons if not defined individually.
		 */
		theme: String,
		/**
		 * Specific size styling - see `<k-button>` for details.
		 * Default for buttons if not defined individually.
		 */
		size: String,
		/**
		 * Whether to show text on small screens - see `<k-button>` for details.
		 * Default for buttons if not defined individually.
		 */
		responsive: Boolean
	}
};
</script>

<style>
.k-button-group {
	display: flex;
	flex-wrap: wrap;
	gap: 0.5rem;
	align-items: center;
}

/**
 * layout: collapsed
 */
.k-button-group:where([data-layout="collapsed"]) {
	gap: 1px;
}

.k-button-group[data-layout="collapsed"]
	> .k-button[data-variant="filled"]:has(
		+ .k-button[data-variant="filled"],
		+ details > .k-button[data-variant="filled"]
	) {
	border-start-end-radius: 0;
	border-end-end-radius: 0;
}

.k-button-group[data-layout="collapsed"]
	> .k-button[data-variant="filled"]
	+ .k-button[data-variant="filled"],
.k-button-group[data-layout="collapsed"]
	> .k-button[data-variant="filled"]
	+ details
	> .k-button[data-variant="filled"] {
	border-start-start-radius: 0;
	border-end-start-radius: 0;
}
</style>
