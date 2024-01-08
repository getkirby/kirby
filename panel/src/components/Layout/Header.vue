<template>
	<header
		class="k-header"
		:data-has-buttons="Boolean($slots.buttons || $slots.left || $slots.right)"
	>
		<h1 class="k-header-title">
			<!--
				Edit button has been clicked
				@event edit
			-->
			<button
				v-if="editable"
				class="k-header-title-button"
				type="button"
				@click="$emit('edit')"
			>
				<span class="k-header-title-text">
					<!-- @slot Headline text -->
					<slot />
				</span>
				<span class="k-header-title-icon"><k-icon type="edit" /></span>
			</button>
			<span v-else class="k-header-title-text"><slot /></span>
		</h1>

		<div
			v-if="$slots.buttons || $slots.left || $slots.right"
			class="k-header-buttons"
		>
			<!-- @slot Position for optional buttons opposite the headline -->
			<slot name="buttons" />

			<!--
				@slot
				@deprecated 4.0.0 left slot, use buttons slot instead
			-->
			<slot name="left" />
			<!--
				@slot
				@deprecated 4.0.0 right slot, use buttons slot instead
			-->
			<slot name="right" />
		</div>
	</header>
</template>

<script>
/**
 * Sticky header containing headline (with optional edit button)
 * and optional buttons
 *
 * @example <k-header :editable="true">Headline</k-header>
 * @example <k-header>
 * 	Headline
 *
 * 	<k-button-group slot="buttons">
 * 		<k-button icon="open" variant="filled" />
 * 		<k-button icon="cog" variant="filled" />
 * 	</k-button-group>
 * </k-header>
 */
export default {
	props: {
		/**
		 * Whether the headline is editable
		 */
		editable: {
			type: Boolean
		},
		/**
		 * @deprecated 4.0.0 Has no effect anymore, use `k-tabs` as standalone component instead
		 */
		tabs: Array
	},
	emits: ["edit"],
	created() {
		if (this.tabs) {
			window.panel.deprecated(
				"<k-header>: `tabs` prop isn't supported anymore and has no effect. Use `<k-tabs>` as standalone component instead."
			);
		}

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
	--header-sticky-offset: calc(var(--scroll-top) + 4rem);
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
	--icon-color: var(--color-text-dimmed);
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
	top: var(--scroll-top);
	z-index: var(--z-toolbar);
}
</style>
