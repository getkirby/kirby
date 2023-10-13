<template>
	<k-panel-inside class="k-lab-docs-view">
		<k-header>
			{{ component }}

			<k-button-group v-if="docs.github" slot="buttons">
				<k-button
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
import Vue from "vue";

import Docs from "./Docs.vue";
import DocsDrawer from "./DocsDrawer.vue";

Vue.component("k-lab-docs", Docs);
Vue.component("k-lab-docs-drawer", DocsDrawer);

export default {
	props: {
		component: String,
		docs: Object
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
