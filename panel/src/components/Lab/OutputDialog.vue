<template>
	<k-dialog
		v-bind="$attrs"
		size="large"
		:cancel-button="false"
		:submit-button="false"
		@cancel="$emit('cancel')"
	>
		<k-code :language="language">{{ code }}</k-code>
	</k-dialog>
</template>

<script>
export default {
	props: {
		code: String,
		language: {
			default: "js",
			type: String
		}
	},
	emits: ["cancel"]
};
</script>
