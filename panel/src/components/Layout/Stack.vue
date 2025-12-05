<template>
	<div
		class="k-stack"
		:style="{
			alignItems: align,
			flexDirection: direction,
			gap: gap,
			justifyContent: justify
		}"
	>
		<slot />
	</div>
</template>

<script>
export default {
	props: {
		align: String,
		direction: String,
		gap: String,
		justify: String
	}
};
</script>

<style>
:where(.k-stack) {
	display: flex;
	flex-direction: column;
	container-type: inline-size;
	gap: var(--spacing-6);
}
</style>
