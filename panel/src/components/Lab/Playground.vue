<template>
	<k-panel-inside :data-has-tabs="tabs.length > 1" class="k-ui-playground-view">
		<k-header>
			{{ title }}
			<k-button-group v-if="docs" slot="buttons">
				<k-button icon="book" size="sm" variant="filled" @click="openDocs">
					Docs
				</k-button>
			</k-button-group>
		</k-header>
		<k-tabs :tab="tab" :tabs="tabs" />

		<component :is="component" v-if="file" v-bind="props" />
		<!-- eslint-disable-next-line vue/no-v-html, vue/no-v-text-v-html-on-component -->
		<component :is="'style'" v-if="styles" v-html="styles" />
	</k-panel-inside>
</template>

<script>
export default {
	props: {
		docs: String,
		examples: Object,
		file: String,
		props: Object,
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
			this.$panel.drawer.open({
				component: "k-ui-docs-drawer",
				props: {
					icon: "book",
					title: this.docs,
					docs: this.docs
				}
			});
		}
	}
};
</script>

<style>
.k-ui-playground-view[data-has-tabs="true"] .k-header {
	margin-bottom: 0;
}
</c>
