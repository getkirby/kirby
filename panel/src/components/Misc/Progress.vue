<template>
	<progress :value="state" max="100" class="k-progress">{{ state }}%</progress>
</template>

<script>
/**
 * Validates the value to be between 0 and 100
 * @param {Number} value
 * @param {Boolean} throws whether to throw an error when validation fails
 * @returns {Boolean}
 */
const validator = (value, throws = false) => {
	if (value >= 0 && value <= 100) {
		return true;
	}

	if (throws) {
		throw new Error("value has to be between 0 and 100");
	}

	return false;
};

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
			validator: validator
		}
	},
	data() {
		return {
			state: this.value
		};
	},
	watch: {
		value(value) {
			this.state = value;
		}
	},
	methods: {
		/**
		 * Update the value
		 * @param {Number} value new value of the progress bar (0-100)
		 * @deprecated 4.0.0 Use `value` prop instead
		 */
		set(value) {
			window.panel.deprecated(
				"<k-dprogress>: `set` method will be removed in a future version. Use the `value` prop instead."
			);

			validator(value, true);
			this.state = value;
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
