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
.k-dialog {
	position: relative;
	background: var(--color-background);
	width: 100%;
	box-shadow: var(--shadow-lg);
	border-radius: var(--rounded-md);
	line-height: 1;
	max-height: calc(100vh - 3rem);
	margin: 1.5rem;
	display: flex;
	flex-direction: column;
}

@media screen and (min-width: 20rem) {
	.k-dialog[data-size="small"] {
		width: 20rem;
	}
}

@media screen and (min-width: 22rem) {
	.k-dialog[data-size="default"] {
		width: 22rem;
	}
}

@media screen and (min-width: 30rem) {
	.k-dialog[data-size="medium"] {
		width: 30rem;
	}
}

@media screen and (min-width: 40rem) {
	.k-dialog[data-size="large"] {
		width: 40rem;
	}
}
</style>
