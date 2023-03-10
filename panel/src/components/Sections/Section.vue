<template>
	<section class="k-section">
		<header v-if="headline || buttons" class="k-bar k-section-header">
			<k-headline :link="link" tag="h2" class="k-label">
				{{ headline }}
				<abbr v-if="required" :title="$t('section.required')">*</abbr>
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
.k-section {
	padding-bottom: 3rem;
}
.k-section-header {
	margin-bottom: var(--spacing-2);
}
.k-label {
	position: relative;
	display: flex;
	font-weight: var(--font-semi);
	align-items: center;
	height: var(--height-xs);
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
}
.k-label abbr {
	text-decoration: none;
	color: var(--color-gray-500);
	padding-inline-start: 0.25rem;
}
[aria-disabled] .k-label {
	opacity: var(--opacity-disabled);
	cursor: not-allowed;
}
</style>
