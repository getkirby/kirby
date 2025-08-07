<template>
	<k-dialog
		ref="dialog"
		v-bind="$props"
		:cancel-button="false"
		:submit-button="{ theme: 'passive' }"
		class="k-lock-alert-dialog"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-dialog-text :text="$t('form.locked')" />

		<dl>
			<div>
				<dt><k-icon type="user" /></dt>
				<dd>{{ lock.user.email }}</dd>
			</div>
			<div>
				<dt><k-icon type="clock" /></dt>
				<dd>
					{{ $library.dayjs(lock.modified).format("YYYY-MM-DD HH:mm:ss") }}
				</dd>
			</div>
		</dl>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export const props = {
	mixins: [Dialog],
	props: {
		cancelButton: null,
		submitButton: null,

		lock: Object,
		preview: String
	}
};

export default {
	mixins: [props],
	emits: ["cancel", "submit"]
};
</script>

<style>
.k-lock-alert-dialog dl {
	margin: var(--spacing-6) 0 var(--spacing-2) 0;
}
.k-lock-alert-dialog dl div {
	padding: var(--spacing-1) 0;
	line-height: var(--leading-normal);
	display: flex;
	align-items: center;
	gap: 0.75rem;
	color: var(--color-gray-500);
}
.k-lock-alert-dialog .k-dialog-buttons {
	grid-template-columns: 1fr;
}
</style>
