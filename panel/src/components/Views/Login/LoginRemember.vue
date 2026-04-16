<template>
	<k-checkbox-input
		:label="$t('login.remember')"
		:checked="checked"
		:value="checked"
		@input="$emit('input', $event)"
	/>
</template>

<script>
export default {
	props: {
		checked: Boolean
	},
	emits: ["input"]
};
</script>
