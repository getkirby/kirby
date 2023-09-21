<template>
	<span v-if="$slots.default || type" class="k-inputbox-icon">
		<slot>
			<k-icon :type="type" />
		</slot>
	</span>
</template>

<script>
export default {
	props: {
		type: String
	}
};
</script>

<style>
.k-inputbox-icon {
	color: var(--inputbox-color-icon);
	display: flex;
	justify-content: center;
	align-items: center;
	align-self: stretch;
	width: var(--inputbox-height);
	flex-shrink: 0;
}
.k-inputbox-icon-button {
	--button-align: center;
	--button-height: 100%;
	--button-width: 100%;
	--opacity-disabled: 100%;
}
</style>
