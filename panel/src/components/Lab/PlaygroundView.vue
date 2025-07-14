<template>
	<k-panel-inside class="k-lab-playground-view">
		<template #topbar>
			<k-theme-view-button :text="null" :variant="null" size="xs" />
		</template>

		<k-header class="k-lab-playground-header">
			{{ title }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
			</template>
		</k-header>
		<k-tabs :tab="tab" :tabs="tabs" />

		<k-box v-if="compiler === false" theme="info">
			The Vue template compiler must be enabled to show lab examples
		</k-box>
		<template v-else>
			<component :is="component" v-if="component" v-bind="props" />
			<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
			<component :is="'style'" v-if="styles" v-html="styles" />
		</template>
	</k-panel-inside>
</template>

<script>
import { markRaw } from "vue";

import Docs from "./Docs.vue";
import DocsDrawer from "./DocsDrawer.vue";
import Example from "./Example.vue";
import Examples from "./Examples.vue";
import Form from "./Form.vue";
import OutputDialog from "./OutputDialog.vue";
import TableCell from "./TableCell.vue";

export default {
	props: {
		buttons: Array,
		compiler: Boolean,
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
		if (this.$helper.isComponent("k-lab-docs") === false) {
			window.panel.app.component("k-lab-docs", Docs);
			window.panel.app.component("k-lab-docs-drawer", DocsDrawer);
			window.panel.app.component("k-lab-example", Example);
			window.panel.app.component("k-lab-examples", Examples);
			window.panel.app.component("k-lab-form", Form);
			window.panel.app.component("k-lab-output-dialog", OutputDialog);
			window.panel.app.component("k-lab-table-cell", TableCell);
		}

		const path = this.$panel.view.path.replace(/lab\//, "");
		import.meta.hot?.on("kirby:example:" + path, this.reloadComponent);
		import.meta.hot?.on("kirby:docs:" + this.docs, this.reloadDocs);
	},
	methods: {
		async createComponent() {
			if (!this.file) {
				return;
			}

			const { default: component } = await import(
				/* @vite-ignore */
				this.$panel.url(this.file) + "?cache=" + Date.now()
			);

			// add the template to the component
			component.template = this.template;

			// unwrap to be recognized as new component
			this.component = markRaw({ ...component });

			// update the code strings for each example
			window.UiExamples = this.examples;
		},
		async reloadComponent() {
			await this.$panel.view.refresh();
			this.createComponent();
		},
		reloadDocs() {
			if (this.$panel.drawer.isOpen) {
				this.$panel.drawer.refresh();
			}
		}
	}
};
</script>

<style>
.k-lab-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-lab-examples * + h2 {
	margin-top: var(--spacing-12);
}

:where(.k-lab-input-examples, .k-lab-field-examples)
	.k-lab-example:has(:invalid) {
	outline: 2px solid var(--color-red-500);
	outline-offset: -2px;
}

.k-lab-input-examples-focus .k-lab-example-canvas > .k-button {
	margin-top: var(--spacing-6);
}

.k-lab-helpers-examples .k-lab-example .k-text {
	margin-bottom: var(--spacing-6);
}

.k-lab-helpers-examples h2 {
	margin-bottom: var(--spacing-3);
	font-weight: var(--font-bold);
}
</style>
