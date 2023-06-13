<template>
	<k-overlay
		ref="dialog"
		:centered="true"
		:dimmed="true"
		:visible="visible"
		type="dialog"
		@cancel="cancel"
		@ready="ready"
	>
		<form
			:class="$vnode.data.staticClass"
			:data-size="size"
			class="k-dialog"
			method="dialog"
			@submit.prevent="submit"
		>
			<k-dialog-notification />
			<k-dialog-body>
				<slot />
			</k-dialog-body>
			<slot name="footer">
				<k-dialog-footer v-if="cancelButton || submitButton">
					<k-dialog-buttons
						:cancel-button="cancelButton"
						:disabled="disabled"
						:icon="icon"
						:submit-button="submitButton"
						:theme="theme"
						@cancel="cancel"
						@submit="submit"
					/>
				</k-dialog-footer>
			</slot>
		</form>
	</k-overlay>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * Modal dialogs are used in Kirby's Panel in many places for quick actions like adding new pages, changing titles, etc. that don't necessarily need a full new view. You can create your own modals for your fields and other plugins or reuse our existing modals to invoke typical Panel actions.
 */
export default {
	mixins: [Dialog]
};
</script>

<style>
:root {
	--dialog-color-back: var(--color-light);
	--dialog-color-text: currentColor;
	--dialog-rounded: var(--rounded-xl);
	--dialog-padding: var(--spacing-6);
	--dialog-shadow: var(--shadow-xl);
	--dialog-width: 22rem;
}

.k-dialog {
	position: relative;
	background: var(--dialog-color-back);
	color: var(--dialog-color-text);
	width: clamp(10rem, 100%, var(--dialog-width));
	box-shadow: var(--dialog-shadow);
	border-radius: var(--dialog-rounded);
	line-height: 1;
	max-height: calc(100vh - 3rem);
	margin: 1.5rem;
	display: flex;
	flex-direction: column;
}

@media screen and (min-width: 20rem) {
	.k-dialog[data-size="small"] {
		--dialog-width: 20rem;
	}
}

@media screen and (min-width: 22rem) {
	.k-dialog[data-size="default"] {
		--dialog-width: 22rem;
	}
}

@media screen and (min-width: 30rem) {
	.k-dialog[data-size="medium"] {
		--dialog-width: 30rem;
	}
}

@media screen and (min-width: 40rem) {
	.k-dialog[data-size="large"] {
		--dialog-width: 40rem;
	}
}

/** Pagination **/
.k-dialog .k-pagination {
	margin-bottom: -1.5rem;
	display: flex;
	justify-content: center;
	align-items: center;
}
</style>
