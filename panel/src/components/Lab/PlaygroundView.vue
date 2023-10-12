<template>
	<k-panel-inside
		:data-has-tabs="tabs.length > 1"
		class="k-lab-playground-view"
	>
		<k-header>
			{{ title }}
			<k-button-group v-if="docs || github" slot="buttons">
				<k-button
					v-if="docs"
					:text="docs"
					icon="book"
					size="sm"
					variant="filled"
					@click="openDocs"
				/>
				<k-button
					v-if="github"
					icon="github"
					size="sm"
					variant="filled"
					:link="github"
					target="_blank"
				/>
			</k-button-group>
		</k-header>
		<k-tabs :tab="tab" :tabs="tabs" />

		<component :is="component" v-if="file" v-bind="props" />
		<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
		<component :is="'style'" v-if="styles" v-html="styles" />
	</k-panel-inside>
</template>

<script>
import Vue from "vue";

import Docs from "./Docs.vue";
import DocsDrawer from "./DocsDrawer.vue";
import Example from "./Example.vue";
import Examples from "./Examples.vue";
import FieldExamples from "./FieldExamples.vue";
import FieldPreviewExample from "./FieldPreviewExample.vue";
import Form from "./Form.vue";
import InputExamples from "./InputExamples.vue";
import InputboxExamples from "./InputboxExamples.vue";
import OptionsFieldExamples from "./OptionsFieldExamples.vue";
import OptionsInputExamples from "./OptionsInputExamples.vue";
import OptionsInputboxExamples from "./OptionsInputboxExamples.vue";
import OutputDialog from "./OutputDialog.vue";

Vue.component("k-lab-docs", Docs);
Vue.component("k-lab-docs-drawer", DocsDrawer);
Vue.component("k-lab-example", Example);
Vue.component("k-lab-examples", Examples);
Vue.component("k-lab-field-examples", FieldExamples);
Vue.component("k-lab-field-preview-example", FieldPreviewExample);
Vue.component("k-lab-form", Form);
Vue.component("k-lab-input-examples", InputExamples);
Vue.component("k-lab-inputbox-examples", InputboxExamples);
Vue.component("k-lab-options-field-examples", OptionsFieldExamples);
Vue.component("k-lab-options-input-examples", OptionsInputExamples);
Vue.component("k-lab-options-inputbox-examples", OptionsInputboxExamples);
Vue.component("k-lab-output-dialog", OutputDialog);

export default {
	props: {
		docs: String,
		examples: [Object, Array],
		file: String,
		github: String,
		props: [Object, Array],
		styles: String,
		tab: String,
		tabs: {
			type: Array,
			default: () => []
		},
		template: String,
		title: String
	},
	data() {
		return {
			component: null
		};
	},
	watch: {
		tab: {
			handler() {
				this.createComponent();
			},
			immediate: true
		}
	},
	mounted() {
		import.meta.hot?.on("kirby:docs:reload", this.reloadDocs);
	},
	methods: {
		async createComponent() {
			if (!this.file) {
				return;
			}

			const component = await import(
				/* @vite-ignore */
				this.$panel.url(this.file)
			);

			// add the template to the component
			component.default.template = this.template;

			this.component = component.default;

			// update the code strings for each example
			window.UiExamples = this.examples;
		},
		openDocs() {
			this.$panel.drawer.open(`lab/docs/${this.docs}`);
		},
		reloadDocs() {
			if (this.$panel.drawer.isOpen) {
				this.$panel.drawer.close();
				this.openDocs();
			}
		}
	}
};
</script>

<style>
.k-lab-playground-view[data-has-tabs="true"] .k-header {
	margin-bottom: 0;
}
</style>
