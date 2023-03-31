<template>
	<div :data-size="size" class="k-dialog" @mousedown.stop>
		<slot />
	</div>
</template>

<script>
export const props = {
	props: {
		/**
		 * The modal size can be adjusted with the size prop
		 * @values small, medium, large
		 */
		size: {
			default: "default",
			type: String
		}
	}
};

export default {
	mixins: [props]
};
</script>

<style>
:root {
	--dialog-color-back: var(--color-light);
	--dialog-color-text: currentColor;
	--dialog-rounded: var(--rounded-md);
	--dialog-padding: var(--spacing-6);
	--dialog-shadow: var(--shadow-xl);
	--dialog-width: 22rem;
}

.k-dialog {
	position: relative;
	background: var(--dialog-color-back);
	color: var(--dialog-color-text);
	width: clamp(10rem, 100%, var(--dialog-width));
	box-shadow: var(--dialog-shadow);
	border-radius: var(--dialog-rounded);
	line-height: 1;
	max-height: calc(100vh - 3rem);
	margin: 1.5rem;
	display: flex;
	flex-direction: column;
}

@media screen and (min-width: 20rem) {
	.k-dialog[data-size="small"] {
		--dialog-width: 20rem;
	}
}

@media screen and (min-width: 22rem) {
	.k-dialog[data-size="default"] {
		--dialog-width: 22rem;
	}
}

@media screen and (min-width: 30rem) {
	.k-dialog[data-size="medium"] {
		--dialog-width: 30rem;
	}
}

@media screen and (min-width: 40rem) {
	.k-dialog[data-size="large"] {
		--dialog-width: 40rem;
	}
}
</style>
