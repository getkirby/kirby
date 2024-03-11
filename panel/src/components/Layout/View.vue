<template>
	<div :data-align="align" class="k-view">
		<!-- @slot where your content goes -->
		<slot />
	</div>
</template>

<script>
/**
 * @deprecated 4.0.0
 */
export default {
	props: {
		/**
		 * To center the content
		 * @values center
		 */
		align: String
	},
	mounted() {
		window.panel.deprecated("<k-view> will be removed in a future version.");
	}
};
</script>

<style>
.k-view {
	padding-inline: 1.5rem;
}

@container (min-width: 30rem) {
	.k-view {
		padding-inline: 3rem;
	}
}

.k-view[data-align="center"] {
	height: 100vh;
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 0 3rem;
	overflow: auto;
}
.k-view[data-align="center"] > * {
	flex-basis: 22.5rem;
}
</style>
