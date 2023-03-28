<template>
	<header :data-editable="editable" class="k-header">
		<k-headline tag="h1" @click="isEditable ? $emit('edit') : null">
			<slot />
			<k-icon type="edit" />
		</k-headline>

		<k-bar
			v-if="$slots.buttons || $slots.left || $slots.right"
			class="k-header-buttons"
		>
			<slot name="buttons" />
			<!-- @deprecated left/right slot, use buttons slot instead -->
			<!-- @todo remove right/left slots @ 5.0 -->
			<slot name="left" />
			<slot name="right" />
		</k-bar>
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
}

.k-header {
	border-bottom: 1px solid var(--color-border);
	margin-bottom: var(--spacing-12);
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
	margin-bottom: var(--spacing-6);
	line-height: var(--leading-h1);
	cursor: pointer;
}

.k-header h1 svg {
	color: var(--color-gray-500);
	opacity: 0;
	transition: opacity 0.2s;
}

.k-header[data-editable="true"] h1:hover svg {
	opacity: 1;
}

.k-header:has(.k-header-buttons) {
	position: sticky;
	top: calc((var(--text-h1) + var(--spacing-6)) * -1);
	background-color: var(--color-light);
	z-index: var(--z-toolbar);
}
.k-header:has(.k-header-buttons)::before,
.k-header:has(.k-header-buttons)::after {
	position: absolute;
	inset-block: 0;
	width: var(--spacing-1);
	background-color: var(--color-light);
	content: "";
}
.k-header:has(.k-header-buttons)::before {
	inset-inline-start: calc(var(--spacing-1) * -1);
}
.k-header:has(.k-header-buttons)::after {
	inset-inline-end: calc(var(--spacing-1) * -1);
}

.k-header .k-header-buttons {
	--bar-height: var(--header-bar-height);
	margin-bottom: var(--spacing-1);
}
</style>
