<template>
	<k-checkbox-input
		:label="$t('login.remember')"
		:checked="checked"
		:value="checked"
		@input="$emit('input', $event)"
	/>
</template>

<script>
/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	props: {
		checked: Boolean
	},
	emits: ["input"]
};
</script>
