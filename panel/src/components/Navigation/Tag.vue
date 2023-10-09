<template>
	<button
		ref="button"
		:aria-disabled="disabled"
		:data-has-toggle="removable"
		class="k-tag"
		type="button"
		@keydown.delete.prevent="remove"
	>
		<slot name="image">
			<k-image-frame v-if="image?.src" v-bind="image" class="k-tag-image" />
			<k-icon-frame v-else-if="image" v-bind="image" class="k-tag-image" />
		</slot>

		<span v-if="$slots.default" class="k-tag-text">
			<slot />
		</span>

		<k-icon-frame
			v-if="removable"
			class="k-tag-toggle"
			icon="cancel-small"
			@click.native="remove"
		/>
	</button>
</template>

<script>
/**
 * The Tag Button is mostly used in the `<k-tags-input>` component
 * @example <k-tag>Design</k-tag>
 */
export default {
	props: {
		disabled: Boolean,
		image: {
			type: Object
		},
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
	--tag-color-back: var(--color-black);
	--tag-color-text: var(--color-white);
	--tag-color-toggle: currentColor;
	--tag-color-disabled-back: var(--color-gray-600);
	--tag-color-disabled-text: var(--tag-color-text);
	--tag-height: var(--height-xs);
	--tag-rounded: var(--rounded-sm);
}

.k-tag {
	position: relative;
	height: var(--tag-height);
	display: flex;
	align-items: center;
	justify-content: space-between;
	font-size: var(--text-sm);
	line-height: 1;
	color: var(--tag-color-text);
	background-color: var(--tag-color-back);
	border-radius: var(--tag-rounded);
	cursor: pointer;
	user-select: none;
}
.k-tag:focus {
	outline: var(--outline);
}
.k-tag-image {
	height: calc(var(--tag-height) - var(--spacing-2));
	margin-inline-start: var(--spacing-1);
	border-radius: var(--tag-rounded);
	overflow: hidden;
}
.k-tag-text {
	padding-inline: 0.5rem;
	line-height: var(--leading-tight);
}
.k-tag-toggle {
	width: var(--tag-height);
	height: var(--tag-height);
	filter: brightness(70%);
}
.k-tag-toggle:hover {
	filter: brightness(100%);
}
/** TODO: .k-tag:has(.k-tag-toggle) .k-tag-text  */
.k-tag[data-has-toggle="true"] .k-tag-text {
	padding-inline-end: 0;
}

.k-tag:where([aria-disabled]) {
	background-color: var(--tag-color-disabled-back);
	color: var(--tag-color-disabled-text);
	cursor: not-allowed;
}
.k-tag:where([aria-disabled]) .k-tag-toggle {
	display: none;
}
</style>
