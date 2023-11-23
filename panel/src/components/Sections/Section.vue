<template>
	<section class="k-section" :data-invalid="invalid">
		<header v-if="headline || buttons" class="k-section-header">
			<k-label
				:invalid="invalid"
				:link="link"
				:required="required"
				type="section"
			>
				{{ headline }}
			</k-label>
			<k-button-group
				v-if="buttons"
				:buttons="buttons"
				size="xs"
				variant="filled"
				class="k-section-buttons"
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
		invalid: Boolean,
		link: String,
		required: Boolean
	}
};
</script>

<style>
.k-section + .k-section {
	margin-top: var(--columns-block-gap);
}
.k-section-header {
	display: flex;
	justify-content: space-between;
	align-items: center;
	gap: var(--spacing-6);
	margin-bottom: var(--spacing-2);
}
.k-section-buttons {
	flex-shrink: 0;
}
</style>
