<template>
	<div @dblclick="!fieldset.wysiwyg ? $emit('open') : null">
		<header class="k-block-type-fields-header">
			<k-block-title
				:content="values"
				:fieldset="fieldset"
				@dblclick.native="open"
			/>
			<k-drawer-tabs :tab="tab" :tabs="fieldset.tabs" @open="tab = $event" />
		</header>

		<k-form
			ref="form"
			:autofocus="true"
			:disabled="!fieldset.wysiwyg"
			:fields="fields"
			:value="values"
			class="k-block-type-fields-form"
			@input="$emit('update', $event)"
		/>
	</div>
</template>

<script>
/**
 * @displayName BlockTypeFields
 * @internal
 */
export default {
	props: {
		endpoints: Object,
		tabs: Object
	},
	data() {
		return {
			tab: Object.keys(this.tabs)[0]
		};
	},
	computed: {
		fields() {
			return this.tabs[this.tab]?.fields;
		},
		values() {
			return Object.assign({}, this.content);
		}
	},
	methods: {
		open() {
			this.$emit("open", this.tab);
		}
	}
};
</script>

<style>
.k-block-container:has(.k-block-type-fields) {
	padding-top: 0;
}

.k-block-type-fields-header {
	display: flex;
	justify-content: space-between;
	height: 2.5rem;
	padding-inline: var(--spacing-3);
	background: var(--color-white);
	border-start-start-radius: var(--rounded);
	border-start-end-radius: var(--rounded);
}

.k-block-type-fields-header .k-button {
	height: 2.5rem;
}

.k-block-type-fields-form {
	background-color: #eeeff2;
	padding: var(--spacing-6) var(--spacing-6) var(--spacing-8);
	border-radius: var(--rounded);
}

.k-block-container[data-hidden="true"] {
	padding-bottom: 0;
}

.k-block-container[data-hidden="true"]
	:where(.k-drawer-tabs, .k-block-type-fields-form) {
	display: none;
}
</style>
