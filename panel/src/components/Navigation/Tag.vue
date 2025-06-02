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

		<template v-if="text">
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span v-if="html" class="k-tag-text" v-html="text" />
			<span v-else class="k-tag-text">{{ text }}</span>
		</template>
		<template v-else-if="$slots.default">
			<span class="k-tag-text">
				<!-- @slot Tag text -->
				<slot />
			</span>
		</template>

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
		 * If set to `true`, the `text` is rendered as HTML code,
		 * otherwise as plain text
		 */
		html: {
			type: Boolean
		},
		/**
		 * See `k-image-frame` or `k-icon-frame` for available options
		 */
		image: {
			type: Object
		},
		/**
		 * Enables the remove button
		 */
		removable: Boolean,
		/**
		 * Text to display in the bubble
		 */
		text: String
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
	max-width: 100%;
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
.k-tag:not([aria-disabled]):focus {
	outline: var(--outline);
}
.k-tag-image {
	height: 100%;
	border-radius: var(--rounded-xs);
	overflow: hidden;
	flex-shrink: 0;
	border-radius: 0;
	border-start-start-radius: var(--tag-rounded);
	border-end-start-radius: var(--tag-rounded);
	background-clip: padding-box;
}
.k-tag-text {
	padding-inline: var(--spacing-2);
	line-height: var(--leading-tight);
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
}
/** TODO: .k-tag:has(.k-tag-toggle) .k-tag-text  */
.k-tag[data-has-toggle="true"] .k-tag-text {
	padding-inline-end: 0;
}
.k-tag-toggle {
	--icon-size: 14px;
	width: var(--tag-height);
	height: var(--tag-height);
	filter: brightness(70%);
	flex-shrink: 0;
}
.k-tag-toggle:hover {
	filter: brightness(100%);
}

.k-tag:where([aria-disabled]) {
	background-color: var(--tag-color-disabled-back);
	color: var(--tag-color-disabled-text);
	cursor: not-allowed;
}
</style>
