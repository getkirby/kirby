<template>
	<figcaption class="k-block-figure-caption">
		<k-writer-input
			:disabled="disabled"
			:inline="true"
			:marks="marks"
			:spellcheck="false"
			:value="value"
			@input="$emit('input', $event)"
		/>
	</figcaption>
</template>

<script>
export default {
	props: {
		disabled: Boolean,
		marks: [Array, Boolean],
		value: String
	}
};
</script>

<style>
.k-block-figure-caption {
	display: flex;
	justify-content: center;
	padding-top: var(--spacing-3);
}
.k-block-figure-caption .k-writer {
	width: max-content;
	text-align: center;
}

.k-block-figure-caption .k-writer .k-text {
	color: var(--color-gray-600);
	font-size: var(--text-sm);
	mix-blend-mode: exclusion;
}
</style>
