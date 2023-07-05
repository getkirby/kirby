<template>
	<section class="k-section">
		<header v-if="headline || buttons" class="k-bar k-section-header">
			<k-headline :link="link" tag="h2" class="k-label">
				{{ headline }}
				<abbr v-if="required" :title="$t('section.required')">✶</abbr>
			</k-headline>
			<k-button-group
				v-if="buttons"
				:buttons="buttons"
				size="xs"
				variant="filled"
			/>
		</header>

		<slot />
	</section>
</template>

<script>
export default {
	props: {
		buttons: Array,
		headline: String,
		link: String,
		required: Boolean
	}
};
</script>

<style>
.k-section + .k-section {
	margin-top: var(--spacing-8);
}
.k-section-header {
	margin-bottom: var(--spacing-2);
}
.k-label {
	position: relative;
	display: flex;
	align-items: center;
	height: var(--height-xs);
	overflow: clip;
	text-overflow: ellipsis;
	white-space: nowrap;
	font-weight: var(--font-semi);
}
.k-label abbr {
	margin-top: 2px;
	text-decoration: none;
	font-size: var(--text-xs);
	color: var(--color-gray-600);
	margin-inline-start: 0.5rem;
}
[aria-disabled] .k-label {
	opacity: var(--opacity-disabled);
	cursor: not-allowed;
}
</style>
