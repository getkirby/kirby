<template>
	<Teleport v-if="visible" to=".k-dialog-portal">
		<form
			:class="['k-dialog', $attrs.class]"
			:data-size="size"
			method="dialog"
			@click.stop
			@submit.prevent="$emit('submit')"
		>
			<slot name="header">
				<k-dialog-notification />
			</slot>

			<k-dialog-body v-if="$slots.default">
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
						@cancel="$emit('cancel')"
					/>
				</k-dialog-footer>
			</slot>
		</form>
	</Teleport>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * Modal dialogs are used in Kirby's Panel in many places for quick actions like adding new pages, changing titles, etc. that don't necessarily need a full new view. You can create your own modals for your fields and other plugins or reuse our existing modals to invoke typical Panel actions.
 */
export default {
	mixins: [Dialog],
	emits: ["cancel", "submit"]
};
</script>

<style>
:root {
	--dialog-color-back: var(--panel-color-back);
	--dialog-color-text: currentColor;
	--dialog-margin: var(--spacing-6);
	--dialog-padding: var(--spacing-6);
	--dialog-rounded: var(--rounded-xl);
	--dialog-shadow: var(--shadow-xl);
	--dialog-width: 22rem;
}

.k-dialog-portal {
	padding: var(--dialog-margin);
}

.k-dialog {
	position: relative;
	background: var(--dialog-color-back);
	color: var(--dialog-color-text);
	width: clamp(10rem, 100%, var(--dialog-width));
	box-shadow: var(--dialog-shadow);
	border-radius: var(--dialog-rounded);
	line-height: 1;
	display: flex;
	flex-direction: column;
	overflow: clip;
	container-type: inline-size;
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

@media screen and (min-width: 60rem) {
	.k-dialog[data-size="huge"] {
		--dialog-width: 60rem;
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
