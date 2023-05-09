<template>
	<input v-bind="$props" :data-variant="variant" type="range" />
</template>

<script>
export default {
	props: {
		autofocus: String,
		form: String,
		id: String,
		max: Number,
		min: Number,
		name: String,
		required: Boolean,
		step: Number,
		value: Number,
		variant: String
	}
};
</script>

<style>
:root {
	--range-thumb-color: var(--color-white);
	--range-thumb-height: 1rem;
	--range-thumb-shadow: rgba(0, 0, 0, 0.1) 0 2px 4px 2px,
		rgba(0, 0, 0, 0.125) 0 0 0 1px;
	--range-track-height: var(--range-thumb-height);
}
input[type="range"] {
	display: flex;
	align-items: center;
	appearance: none;
	height: var(--range-thumb-size);
	background: var(--color-gray-300);
	padding: 0;
	border-radius: var(--range-track-height);
	height: var(--range-track-height);
}
input[type="range"][disabled] {
	--range-thumb-color: rgba(255, 255, 255, 0.2);
}
input[type="range"]::-webkit-slider-thumb {
	appearance: none;
	height: var(--range-thumb-height);
	aspect-ratio: 1/1;
	background: var(--range-thumb-color);
	box-shadow: var(--range-thumb-shadow);
	border-radius: 50%;
	transform: translateY(-50%);
	transform: translate(0, 0);
	z-index: 1;
	cursor: grab;
}
input[type="range"]::-webkit-slider-thumb:active {
	cursor: grabbing;
}
input[type="range"][disabled]::-webkit-slider-thumb {
	cursor: not-allowed;
}
input[type="range"]::-webkit-slider-runnable-track {
	border-radius: var(--range-track-height);
}

/* Focus state */
input[type="range"]:focus::-webkit-slider-thumb {
	outline: var(--field-input-focus-outline);
}

/* Variant: hue */
input[type="range"][data-variant="hue"] {
	background: linear-gradient(
			to right,
			hsl(0, 100%, 50%) 0%,
			hsl(60, 100%, 50%) 16.67%,
			hsl(120, 100%, 50%) 33.33%,
			hsl(180, 100%, 50%) 50%,
			hsl(240, 100%, 50%) 66.67%,
			hsl(320, 100%, 50%) 83.33%,
			hsl(360, 100%, 50%) 100%
		)
		no-repeat;
}

/* Variant: alpha */
input[type="range"][data-variant="alpha"] {
	background: var(--color-white) var(--bg-pattern);
}
input[type="range"][data-variant="alpha"]::-webkit-slider-runnable-track {
	background: linear-gradient(to right, transparent, currentColor) no-repeat;
}
</style>
