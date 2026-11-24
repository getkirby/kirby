<template>
	<k-text-field
		:autofocus="true"
		:counter="false"
		:help="$t('login.code.text.' + type)"
		:label="$t('login.code.label.' + type)"
		:placeholder="$t('login.code.placeholder.' + type)"
		:required="true"
		:value="value"
		autocomplete="one-time-code"
		icon="unlock"
		name="code"
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
		type: String,
		value: String
	},
	emits: ["input"]
};
</script>
