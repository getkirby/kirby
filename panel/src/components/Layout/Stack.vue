<template>
	<div class="k-stack" :style="{ flexDirection: direction, gap }">
		<slot />
	</div>
</template>

<script>
export default {
	props: {
		direction: String,
		gap: String
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
