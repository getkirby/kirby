<template>
	<k-drawer
		ref="drawer"
		class="k-form-drawer"
		v-bind="$attrs"
		:options="options"
		@submit="$emit('cancel')"
	>
		<k-lab-docs v-bind="docs" />
	</k-drawer>
</template>

<script>
export default {
	props: {
		docs: Object
	},
	emits: ["cancel"],
	computed: {
		options() {
			const options = [
				{
					icon: "expand",
					link: "lab/docs/" + this.docs.component
				}
			];

			if (this.docs.github) {
				options.unshift({
					icon: "github",
					link: this.docs.github,
					target: "_blank"
				});
			}

			return options;
		}
	}
};
</script>
