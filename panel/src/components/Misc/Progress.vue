<template>
	<progress :value="value" max="100" class="k-progress">{{ value }}%</progress>
</template>

<script>
/**
 * A simple progress bar that we mostly use it in the upload dialog
 *
 * @example <k-progress :value="10" />
 */
export default {
	props: {
		/**
		 * Current value of the the progress bar
		 * @values 0-100
		 */
		value: {
			type: Number,
			default: 0,
			validator: (value) => value >= 0 && value <= 100
		}
	}
};
</script>

<style>
:root {
	--progress-height: var(--spacing-2);
	--progress-color-back: var(--color-gray-300);
	--progress-color-value: var(--color-focus);
}

progress {
	display: block;
	width: 100%;
	height: var(--progress-height);
	border-radius: var(--progress-height);
	overflow: hidden;
	border: 0;
}

/** Determinate **/
progress::-webkit-progress-bar {
	background: var(--progress-color-back);
}

progress::-webkit-progress-value {
	background: var(--progress-color-value);
	border-radius: var(--progress-height);
}

progress::-moz-progress-bar {
	background: var(--progress-color-value);
}

/**	Indeterminate **/
progress:not([value])::-webkit-progress-bar {
	background: var(--progress-color-value);
}
progress:not([value])::-moz-progress-bar {
	background: var(--progress-color-value);
}
</style>
