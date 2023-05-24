<template>
	<span
		ref="button"
		class="k-tag"
		tabindex="0"
		@keydown.delete.prevent="remove"
	>
		<span class="k-tag-text"><slot /></span>
		<k-icon
			v-if="removable"
			class="k-tag-toggle"
			type="cancel-small"
			@click.native="remove"
		/>
	</span>
</template>

<script>
/**
 * The Tag Button is mostly used in the `<k-tags-input>` component
 * @example <k-tag>Design</k-tag>
 */
export default {
	props: {
		/**
		 * Enables the remove button
		 */
		removable: Boolean
	},
	methods: {
		remove() {
			if (this.removable) {
				/**
				 * This event is emitted when the remove button is being clicked or the tag is focussed and the delete key is entered.
				 */
				this.$emit("remove");
			}
		},
		focus() {
			this.$refs.button.focus();
		}
	}
};
</script>

<style>
:root {
	--tag-color-back: var(--color-gray-900);
	--tag-color-text: var(--color-light);
	--tag-color-focus-back: var(--color-focus);
	--tag-color-focus-text: var(--color-white);
	--tag-color-disabled-back: var(--color-gray-600);
	--tag-color-disabled-text: var(--tag-color-text);
	--tag-rounded: var(--rounded);
	--tag-height: var(--height-sm);
}

.k-tag {
	position: relative;
	height: var(--tag-height);
	font-size: var(--text-sm);
	line-height: 1;
	cursor: pointer;
	background-color: var(--tag-color-back);
	color: var(--tag-color-text);
	border-radius: var(--tag-rounded);
	display: flex;
	align-items: center;
	justify-content: space-between;
	user-select: none;
}
.k-tag:focus {
	outline: 0;
	background-color: var(--tag-color-focus-back);
	color: var(--tag-color-focus-text);
}
.k-tag-text {
	padding-inline: 0.75rem;
	line-height: var(--leading-tight);
}
.k-tag-toggle {
	width: var(--tag-height);
	height: var(--tag-height);
	padding-inline-end: 1px;
	border-inline-start: 1px solid rgba(255, 255, 255, 0.15);
	color: rgba(255, 255, 255, 0.7);
}
.k-tag:has(.k-tag-toggle) .k-tag-text {
	padding-inline-end: 0.125rem;
}
[data-disabled="true"] .k-tag {
	background-color: var(--tag-color-disabled-back);
	color: var(--tag-color-disabled-text);
}
[data-disabled="true"] .k-tag .k-tag-toggle {
	display: none;
}
</style>
