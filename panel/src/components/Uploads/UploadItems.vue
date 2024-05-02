<template>
	<ul class="k-upload-items">
		<k-upload-item
			v-for="item in items"
			:key="item.id"
			v-bind="item"
			@rename="$emit('rename', item, $event)"
			@remove="$emit('remove', item)"
		/>
	</ul>
</template>

<script>
/**
 * @since 4.3.0
 */
export default {
	props: {
		items: Array
	}
};
</script>

<style>
.k-upload-items {
	display: grid;
	gap: 0.25rem;
}
</style>
