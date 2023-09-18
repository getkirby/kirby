<template>
	<input
		ref="range"
		v-bind="$props"
		type="range"
		@input="$emit('input', $event.target.value)"
	/>
</template>

<script>
export default {
	props: {
		autofocus: Boolean,
		form: String,
		id: [Number, String],
		max: Number,
		min: Number,
		name: String,
		required: Boolean,
		step: [Number, String],
		value: [Number, String]
	},
	methods: {
		focus() {
			this.$refs.range.focus();
		}
	}
};
</script>

<style>
:root {
	--range-thumb-color: var(--color-white);
	--range-thumb-size: 1rem;
	--range-thumb-shadow: rgba(0, 0, 0, 0.1) 0 2px 4px 2px,
		rgba(0, 0, 0, 0.125) 0 0 0 1px;
	--range-track-back: var(--color-gray-300);
	--range-track-height: var(--range-thumb-size);
}
input[type="range"] {
	display: flex;
	align-items: center;
	appearance: none;
	padding: 0;
	height: var(--range-thumb-size);
	border-radius: var(--range-thumb-size);
}
input[type="range"]:focus {
	outline: var(--outline);
}
input[type="range"][disabled] {
	--range-thumb-color: rgba(255, 255, 255, 0.2);
}
input[type="range"]::-webkit-slider-thumb {
	appearance: none;
	width: var(--range-thumb-size);
	height: var(--range-thumb-size);
	border: 0;
	background: var(--range-thumb-color);
	box-shadow: var(--range-thumb-shadow);
	transform: translate3d(0, 0, 0);
	margin-top: calc(
		((var(--range-thumb-size) - var(--range-track-height)) / 2) * -1
	);
	border-radius: 50%;
	z-index: 1;
	cursor: grab;
}
input[type="range"]::-moz-range-thumb {
	appearance: none;
	width: var(--range-thumb-size);
	height: var(--range-thumb-size);
	border: 0;
	background: var(--range-thumb-color);
	box-shadow: var(--range-thumb-shadow);
	border-radius: 50%;
	transform: translate3d(0, 0, 0);
	z-index: 1;
	cursor: grab;
}
input[type="range"]::-webkit-slider-thumb:active {
	cursor: grabbing;
}
input[type="range"]::-moz-range-thumb:active {
	cursor: grabbing;
}
input[type="range"][disabled]::-webkit-slider-thumb {
	cursor: not-allowed;
}
input[type="range"][disabled]::-moz-range-thumb {
	cursor: not-allowed;
}
input[type="range"]::-webkit-slider-runnable-track {
	background: var(--range-track-back);
	height: var(--range-track-height);
	border-radius: var(--range-track-height);
}
input[type="range"]::-moz-range-track {
	background: var(--range-track-back);
	height: var(--range-track-height);
	border-radius: var(--range-track-height);
}

/* Focus state */
input[type="range"]:focus::-webkit-slider-thumb {
	outline: var(--outline);
}
input[type="range"]:focus::-moz-range-thumb {
	outline: var(--outline);
}
</style>
