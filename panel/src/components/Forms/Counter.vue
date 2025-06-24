<template>
	<span :data-invalid="!valid" :data-theme="valid ? 'positive' : 'negative'" class="k-counter">
		<span>{{ count }}</span>

		<span v-if="min || max" class="k-counter-rules">
			<template v-if="min && max">{{ min }}–{{ max }}</template>
			<template v-else-if="min">≥ {{ min }}</template>
			<template v-else-if="max">≤ {{ max }}</template>
		</span>
	</span>
</template>

<script>
/**
 * We use the counter in many fields to show the character count of an input or the accepted min/max length of items. You can use the counter yourself very easily:
 * @example <k-counter :count="text.length" :min="2" :max="10" />
<input :value="text" @input="text = $event.target.value">
 */
export default {
	props: {
		count: Number,
		min: Number,
		max: Number,
		required: {
			type: Boolean,
			default: false
		}
	},
	computed: {
		valid() {
			if (this.required === false && this.count === 0) {
				return true;
			}

			if (this.required === true && this.count === 0) {
				return false;
			}

			if (this.min && this.count < this.min) {
				return false;
			}

			if (this.max && this.count > this.max) {
				return false;
			}

			return true;
		}
	}
};
</script>

<style>
.k-counter {
	font-size: var(--text-xs);
	color: var(--color-text);
}
.k-counter[data-theme] {
	color: var(--theme-color-text);
}
.k-counter-rules {
	color: var(--color-text-dimmed);
	padding-inline-start: 0.5rem;
}
</style>
