<template>
	<k-panel-inside class="k-lab-docs-view">
		<template #topbar>
			<k-theme-view-button :text="null" :variant="null" size="xs" />
		</template>

		<k-header>
			{{ component }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
			</template>
		</k-header>

		<k-lab-docs v-bind="docs" />
	</k-panel-inside>
</template>

<script>
import Docs from "./Docs.vue";

export default {
	components: {
		"k-lab-docs": Docs
	},
	props: {
		buttons: Array,
		component: String,
		docs: Object,
		lab: String
	},
	mounted() {
		import.meta.hot?.on("kirby:docs:" + this.component, this.reloadDocs);
	},
	methods: {
		reloadDocs() {
			this.$panel.view.refresh();
		}
	}
};
</script>
