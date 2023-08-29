<template>
	<header
		class="k-header"
		:data-has-buttons="Boolean($slots.buttons || $slots.left || $slots.right)"
	>
		<h1 class="k-header-title">
			<button
				v-if="editable"
				class="k-header-title-button"
				@click="$emit('edit')"
			>
				<span class="k-header-title-text"><slot /></span>
				<span class="k-header-title-icon"><k-icon type="edit" /></span>
			</button>
			<span v-else class="k-header-title-text"><slot /></span>
		</h1>

		<div
			v-if="$slots.buttons || $slots.left || $slots.right"
			class="k-header-buttons"
		>
			<slot name="buttons" />
			<!-- @deprecated 4.0.0 left/right slot, use buttons slot instead -->
			<slot name="left" />
			<slot name="right" />
		</div>
	</header>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		/**
		 * Whether the headline is editable
		 */
		editable: {
			type: Boolean
		}
	},
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
	--header-color-back: var(--color-light);
	--header-padding-block: var(--spacing-4);
	--header-sticky-offset: calc(var(--scroll-top, 0rem) + 4rem);
}

.k-header {
	position: relative;
	display: flex;
	flex-wrap: wrap;
	align-items: baseline;
	justify-content: space-between;
	border-bottom: 1px solid var(--color-border);
	background: var(--header-color-back);
	padding-top: var(--header-padding-block);
	margin-bottom: var(--spacing-12);
	box-shadow:
		2px 0 0 0 var(--header-color-back),
		-2px 0 0 0 var(--header-color-back);
}

.k-header-title {
	font-size: var(--text-h1);
	font-weight: var(--font-h1);
	line-height: var(--leading-h1);
	margin-bottom: var(--header-padding-block);
	min-width: 0;
}

.k-header-title-button {
	display: inline-flex;
	text-align: start;
	gap: var(--spacing-2);
	align-items: baseline;
	max-width: 100%;
	outline: 0;
}

.k-header-title-text {
	overflow-x: clip;
	text-overflow: ellipsis;
}

.k-header-title-icon {
	--icon-color: var(--color-gray-500);
	border-radius: var(--rounded);
	transition: opacity 0.2s;
	display: grid;
	flex-shrink: 0;
	place-items: center;
	height: var(--height-sm);
	width: var(--height-sm);
	opacity: 0;
}

.k-header-title-button:is(:hover, :focus) .k-header-title-icon {
	opacity: 1;
}
.k-header-title-button:is(:focus) .k-header-title-icon {
	outline: var(--outline);
}

.k-header-buttons {
	display: flex;
	flex-shrink: 0;
	gap: var(--spacing-2);
	margin-bottom: var(--header-padding-block);
}

/** TODO: .k-header:has(.k-header-buttons) */
.k-header[data-has-buttons="true"] {
	position: sticky;
	top: var(--scroll-top, 0);
	z-index: var(--z-toolbar);
}
</style>
