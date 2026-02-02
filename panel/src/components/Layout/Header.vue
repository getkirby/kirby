<template>
	<header :data-editable="editable" class="k-header">
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
				<span class="k-header-title-icon">
					<k-icon type="edit" />
				</span>
			</button>
			<span v-else class="k-header-title-text"><slot /></span>
		</h1>

		<div v-if="$slots.buttons" class="k-header-buttons">
			<!-- @slot Position for optional buttons opposite the headline -->
			<slot name="buttons" />
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
 * 	<template #buttons>
 * 		<k-button-group>
 * 			<k-button icon="open" variant="filled" />
 * 			<k-button icon="cog" variant="filled" />
 * 		</k-button-group>
 * 	</template>
 * </k-header>
 */
export default {
	props: {
		/**
		 * Whether the headline is editable
		 */
		editable: Boolean
	},
	emits: ["edit"]
};
</script>

<style>
:root {
	--header-color-back: var(--panel-color-back);
	--header-padding-block: var(--spacing-4);
	--header-sticky-offset: var(--scroll-top);
}

.k-header {
	position: relative;
	display: flex;
	flex-wrap: wrap;
	align-items: baseline;
	justify-content: space-between;
	column-gap: var(--spacing-3);
	border-bottom: 1px solid var(--color-border);
	background: var(--header-color-back);
	padding-top: var(--header-padding-block);
	margin-bottom: var(--spacing-12);
	box-shadow:
		2px 0 0 0 var(--header-color-back),
		-2px 0 0 0 var(--header-color-back);
}

/** Remove the bottom margin from the header if it is followed by tabs */
.k-header:has(+ .k-tabs) {
	margin-bottom: 0;
}

/* On larger screens, keep title and buttons on a single line */
@media screen and (min-width: 70rem) {
	.k-header {
		flex-wrap: nowrap;
	}
}

.k-header-title {
	font-size: var(--text-h1);
	font-weight: var(--font-h1);
	line-height: var(--leading-h1);
	margin-bottom: var(--header-padding-block);
	min-width: 0;
	flex: 1 1 auto;
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
	gap: var(--spacing-2);
	margin-bottom: var(--header-padding-block);
	flex-shrink: 0;
}
.k-header:has(.k-header-buttons) {
	position: sticky;
	top: var(--scroll-top);
	z-index: var(--z-toolbar);
}
:root:has(.k-header .k-header-buttons) {
	--header-sticky-offset: calc(var(--scroll-top) + 4rem);
}

.k-header .k-header-title-placeholder {
	color: var(--color-gray-500);
	transition: color 0.3s;
}
.k-header[data-editable="true"] .k-header-title-placeholder:hover {
	color: var(--color-text-dimmed);
}
</style>
