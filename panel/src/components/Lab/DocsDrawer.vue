<template>
	<k-drawer
		ref="drawer"
		class="k-form-drawer"
		v-bind="$attrs"
		@submit="$emit('cancel')"
	>
		<k-ui-docs v-bind="docs" />
	</k-drawer>
</template>

<script>
export default {
	props: {
		docs: Object
	}
};
</script>
