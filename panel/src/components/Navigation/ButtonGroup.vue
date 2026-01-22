<template>
	<div :data-layout="layout" class="k-button-group">
		<slot v-if="$slots.default" />
		<template v-else>
			<k-button
				v-for="(button, index) in buttons"
				:key="index"
				v-bind="{
					variant,
					theme,
					size,
					responsive,
					...button
				}"
			/>
		</template>
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
		 * @values "collapsed", "dropdown"
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
	gap: var(--spacing-2);
	align-items: center;
}

/**
 * layout: collapsed
 */
.k-button-group:where([data-layout="collapsed"]) {
	gap: 0;
	flex-wrap: nowrap;
}

.k-button-group[data-layout="collapsed"]
	> .k-button[data-variant="filled"]:not(:last-child) {
	border-start-end-radius: 0;
	border-end-end-radius: 0;
}

.k-button-group[data-layout="collapsed"] > .k-button {
	--theme-color-border: var(--panel-color-back);
}

.k-button-group[data-layout="collapsed"]
	> .k-button[data-variant="filled"]:not(:first-child) {
	border-start-start-radius: 0;
	border-end-start-radius: 0;
	border-left: 1px solid var(--theme-color-border);
}

.k-button-group[data-layout="collapsed"]
	> .k-button[data-variant="filled"]:focus-visible {
	z-index: 1;
	border-radius: var(--button-rounded);
}
</style>
