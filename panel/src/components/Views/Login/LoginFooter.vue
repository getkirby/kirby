<template>
	<footer class="k-login-footer">
		<k-login-back v-if="back" />
		<slot />
	</footer>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	props: {
		back: Boolean
	}
};
</script>

<style>
.k-login-footer {
	--button-padding: var(--spacing-3);

	display: flex;
	gap: 1.5rem;
	align-items: center;
	justify-content: space-between;
}
</style>
