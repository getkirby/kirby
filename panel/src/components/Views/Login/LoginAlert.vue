<template>
	<div data-theme="error" class="k-notification k-login-alert">
		<p><slot /></p>
		<k-button icon="cancel" @click="$emit('click')" />
	</div>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	emits: ["click"]
};
</script>

<style>
.k-login-alert {
	border-radius: var(--rounded);
}
</style>
