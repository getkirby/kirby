<template>
	<k-panel-inside>
		<component :is="view" />
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		id: String
	},
	computed: {
		view() {
			return "k-" + this.id + "-plugin-view";
		}
	}
};
</script>
