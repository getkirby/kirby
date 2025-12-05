<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		:visible="visible"
		class="k-validation-error-dialog"
		size="large"
		@cancel="$emit('cancel')"
	>
		<k-stack gap="var(--spacing-6)">
			<k-box icon="alert" theme="notice">{{ message }}</k-box>

			<k-stack>
				<k-headline>Please, fix the following issues:</k-headline>
				<k-validation-issues :fields="fields" />
			</k-stack>
		</k-stack>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	props: {
		fields: Object,
		message: String
	},
	emits: ["cancel"]
};
</script>
