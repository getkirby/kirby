<template>
	<header :data-editable="editable" class="k-header">
		<k-headline tag="h1" @click="isEditable ? $emit('edit') : null">
			<slot />
			<k-icon v-if="editable" type="edit" />
		</k-headline>

		<div
			v-if="$slots.buttons || $slots.left || $slots.right"
			class="k-header-buttons"
		>
			<slot name="buttons" />
			<!-- @deprecated left/right slot, use buttons slot instead -->
			<!-- @todo remove right/left slots @ 5.0 -->
			<slot name="left" />
			<slot name="right" />
		</div>
	</header>
</template>

<script>
/**
 * The Header component is a composition of a big fat headline plus two optional slots for buttons — directly below the headline and on the right. The Header is a fundamental part of any main Panel view. While we use the left slot for option buttons, the right slot is mainly used for prev/next navigation between items such as pages or users.
 * @internal
 */
export default {
	props: {
		/**
		 * Whether the headline is editable
		 */
		editable: Boolean
	},
	computed: {
		isEditable() {
			return this.editable && this.$listeners.edit;
		}
	},
	/**
	 * @todo remove in v5.0 when removing slots
	 */
	mounted() {
		if (this.$slots.left || this.$slots.right) {
			window.panel.deprecated(
				"<k-header>: left/right slots will be removed in a future version. Use `buttons` slot instead."
			);
		}
	}
};
</script>

<style>
:root {
	--header-bar-height: var(--height-md);
	--header-color-back: var(--color-light);
	--header-padding-block: var(--spacing-4);
}

.k-header {
	position: relative;
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	justify-content: space-between;
	border-bottom: 1px solid var(--color-border);
	background: var(--header-color-back);
	padding-top: var(--header-padding-block);
	margin-bottom: var(--spacing-12);
	box-shadow: 2px 0 0 0 var(--header-color-back),
		-2px 0 0 0 var(--header-color-back);
}

.k-header h1 {
	display: inline-flex;
	align-items: baseline;
	gap: var(--spacing-2);
	font-size: var(--text-h1);
	font-weight: var(--font-h1);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	line-height: var(--leading-h1);
	cursor: pointer;
	margin-bottom: var(--header-padding-block);
}

.k-header h1 svg {
	--icon-color: var(--color-gray-500);
	opacity: 0;
	transition: opacity 0.2s;
}

.k-header h1:hover svg {
	opacity: 1;
}

.k-header-buttons {
	display: flex;
	flex-shrink: 0;
	gap: var(--spacing-2);
	margin-bottom: var(--header-padding-block);
}

/* .k-header:has(.k-header-buttons) {
	position: sticky;
	top: 0;
	z-index: var(--z-toolbar);
} */
</style>
