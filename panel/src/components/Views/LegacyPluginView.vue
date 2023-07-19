<template>
	<k-panel-inside>
		<component :is="'k-' + id + '-plugin-view'" />
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		id: String
	}
};
</script>
