<template>
	<div :data-has-styles="Boolean(styles)" @dblclick="$emit('open')">
		<header class="k-block-type-fields-header">
			<k-block-title
				:content="content"
				:fieldset="{ ...fieldset, icon: isLoading ? 'loader' : fieldset.icon }"
			/>
		</header>

		<div
			v-if="snippet !== null"
			class="k-block-type-snippet-html"
			v-html="snippet"
		/>

		<component :is="'style'" v-if="styles" v-html="styles" />
	</div>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeSnippet
 * @since 5.0.0
 */
export default {
	extends: Block,
	props: {
		type: String
	},
	data() {
		return {
			isLoading: false,
			snippet: null,
			styles: null,
			signal: null
		};
	},
	watch: {
		content: {
			handler: function () {
				this.debouncedLoad();
			},
			deep: true
		}
	},
	mounted() {
		this.load();
		this.debouncedLoad = this.$helper.debounce(this.load, 200);
	},
	methods: {
		async load() {
			this.isLoading = true;

			this.signal?.abort();
			this.signal = new AbortController();

			const response = await this.$api.get(
				this.endpoints.field + "/snippet",
				{
					block: this.type,
					content: JSON.stringify(this.content),
					fieldset: JSON.stringify(this.fieldset),
					model: this.endpoints.model
				},
				{
					signal: this.signal.signal
				}
			);
			this.snippet = response.html;
			this.styles = response.styles;

			this.isLoading = false;
		}
	}
};
</script>

<style>
.k-block-container.k-block-container-type-snippet {
	padding-top: 0;
	padding-bottom: var(--spacing-3);
}
.k-block-container-type-snippet .k-block-type-fields-header {
	align-items: center;
}

.k-block-type-snippet-html {
	background-color: var(--color-gray-200);
	padding: var(--spacing-3);
	border-radius: var(--rounded-sm);
	container: column / inline-size;
}

.k-block-container-type-snippet
	[data-has-styles="false"]
	.k-block-type-snippet-html
	* {
	all: initial;
}
</style>
