<template>
	<k-file-preview :details="details">
		<k-icon
			:color="$helper.color(image.color)"
			:type="image.icon"
			class="k-item-icon"
		/>
	</k-file-preview>
</template>

<script>
/**
 * @since 4.3.0
 */
export default {
	props: {
		details: {
			default: () => [],
			type: Array
		},
		image: {
			default: () => ({}),
			type: Object
		}
	}
};
</script>

<style>
.k-file-preview-thumb > .k-icon {
	--icon-size: 3rem;
}
</style>
