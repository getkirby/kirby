<template>
	<k-panel-inside class="k-lab-docs-view">
		<k-header>
			{{ component }}

			<k-button-group v-if="docs.github || lab" slot="buttons">
				<k-button
					v-if="lab"
					icon="lab"
					text="Lab examples"
					size="sm"
					variant="filled"
					:link="'/lab/' + lab"
				/>
				<k-button
					v-if="docs.github"
					icon="github"
					size="sm"
					variant="filled"
					:link="docs.github"
					target="_blank"
				/>
			</k-button-group>
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
