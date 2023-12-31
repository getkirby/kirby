<template>
	<button
		ref="button"
		:aria-disabled="disabled"
		:data-has-image="Boolean(image)"
		:data-has-toggle="isRemovable"
		class="k-tag"
		type="button"
		@keydown.delete.prevent="remove"
	>
		<!-- @slot Replaces the image/icon frame created via the `image` prop -->
		<slot name="image">
			<k-image-frame v-if="image?.src" v-bind="image" class="k-tag-image" />
			<k-icon-frame v-else-if="image" v-bind="image" class="k-tag-image" />
		</slot>

		<span v-if="$slots.default" class="k-tag-text">
			<!-- @slot Tag text -->
			<slot />
		</span>

		<k-icon-frame
			v-if="isRemovable"
			class="k-tag-toggle"
			icon="cancel-small"
			@click.native.stop="remove"
		/>
	</button>
</template>

<script>
/**
 * A simple tag button with optional image/icon and remove button
 *
 * @example <k-tag>Design</k-tag>
 */
export default {
	props: {
		/**
		 * Dims the tag and hides the remove button
		 */
		disabled: Boolean,
		/**
		 * See `k-image-frame` or `k-icon-frame` for available options
		 */
		image: {
			type: Object
		},
		/**
		 * Enables the remove button
		 */
		removable: Boolean
	},
	emits: ["remove"],
	computed: {
		isRemovable() {
			return this.removable && !this.disabled;
		}
	},
	methods: {
		remove() {
			if (this.isRemovable) {
				/**
				 * Remove button is being clicked
				 * or the tag is focussed and the delete key is entered.
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
.k-tag:not([aria-disabled="true"]):focus {
	outline: var(--outline);
}
.k-tag-image {
	height: calc(var(--tag-height) - var(--spacing-2));
	margin-inline: var(--spacing-1);
	border-radius: var(--tag-rounded);
	overflow: hidden;
}
.k-tag-text {
	padding-inline: var(--spacing-2);
	line-height: var(--leading-tight);
}
/** TODO: .k-tag:has(.k-frame) .k-tag-text  */
.k-tag[data-has-image="true"] .k-tag-text {
	padding-inline-start: var(--spacing-1);
}
/** TODO: .k-tag:has(.k-tag-toggle) .k-tag-text  */
.k-tag[data-has-toggle="true"] .k-tag-text {
	padding-inline-end: 0;
}
.k-tag-toggle {
	width: var(--tag-height);
	height: var(--tag-height);
	filter: brightness(70%);
}
.k-tag-toggle:hover {
	filter: brightness(100%);
}

.k-tag:where([aria-disabled="true"]) {
	background-color: var(--tag-color-disabled-back);
	color: var(--tag-color-disabled-text);
	cursor: not-allowed;
}
</style>
