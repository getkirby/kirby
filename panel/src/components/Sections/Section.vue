<template>
	<section class="k-section" :data-invalid="invalid">
		<header
			v-if="label || headline || buttons || $slots.options"
			class="k-section-header"
		>
			<k-label
				:invalid="invalid"
				:link="link"
				:required="required"
				:title="label ?? headline"
				type="section"
			>
				{{ label ?? headline }}
			</k-label>
			<slot name="options">
				<k-button-group
					v-if="buttons"
					:buttons="buttons"
					size="xs"
					variant="filled"
					class="k-section-buttons"
				/>
			</slot>
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
		label: String,
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
