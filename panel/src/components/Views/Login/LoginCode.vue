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
export default {
	props: {
		type: String,
		value: String
	},
	emits: ["input"]
};
</script>
