<template>
	<k-drawer
		ref="drawer"
		class="k-form-drawer"
		v-bind="$attrs"
		@submit="$emit('cancel')"
	>
		<k-ui-docs :component="docs" />
	</k-drawer>
</template>

<script>
export default {
	props: {
		docs: String,
	},
};
</script>
