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
	--progress-color-back: light-dark(var(--color-gray-300), var(--color-gray-850));
	--progress-color-value: var(--color-focus);
}

progress {
	display: block;
	width: 100%;
	height: var(--progress-height);
	border-radius: var(--progress-height);
	overflow: hidden;
	background: var(--progress-color-back);
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
	border-radius: var(--progress-height);
}

/**	Indeterminate **/
progress:not([value])::-webkit-progress-bar {
	background: var(--progress-color-value);
}
progress:not([value])::-moz-progress-bar {
	background: var(--progress-color-value);
}
</style>
