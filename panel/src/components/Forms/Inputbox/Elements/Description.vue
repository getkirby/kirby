<template>
	<span
		v-if="$slots.default || text"
		:class="`k-inputbox-${position}`"
		class="k-inputbox-description"
	>
		<slot>
			{{ text }}
		</slot>
	</span>
</template>

<script>
export default {
	props: {
		position: String,
		text: String
	}
};
</script>

<style>
.k-inputbox-description {
	align-self: stretch;
	display: flex;
	align-items: center;
	flex-shrink: 0;
	color: var(--input-color-description);
	padding-inline: var(--input-padding);
}
.k-inputbox-before {
	padding-inline-end: 0;
}
.k-inputbox-after {
	padding-inline-start: 0;
}

@container (max-width: 30rem) {
	.k-inputbox-description {
		display: none;
	}
}
</style>
