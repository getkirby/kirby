<template>
	<k-dialog
		ref="dialog"
		:cancel-button="false"
		:submit-button="false"
		:size="size"
		:visible="visible"
		class="k-error-dialog"
		@cancel="$emit('cancel')"
	>
		<k-text>{{ message }}</k-text>
		<k-error-details :details="detailsList" />
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	props: {
		details: [Object, Array],
		message: String,
		size: {
			default: "medium",
			type: String
		}
	},
	emits: ["cancel"],
	computed: {
		detailsList() {
			return this.$helper.array.fromObject(this.details);
		}
	}
};
</script>
