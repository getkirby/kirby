<template>
	<div @dblclick="!fieldset.wysiwyg ? $emit('open') : null">
		<header class="k-block-type-fields-header">
			<k-block-title
				:content="values"
				:fieldset="fieldset"
				@dblclick="$emit('open', tab)"
			/>
			<k-drawer-tabs :tab="tab" :tabs="tabs" @tab="tab = $event" />
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
	data() {
		return {
			tab: Object.keys(this.fieldset.tabs)[0]
		};
	},
	computed: {
		hasTabs() {
			return this.tabs.length > 1;
		},
		fields() {
			return this.fieldset.tabs[this.tab].fields;
		},
		tabs() {
			return Object.values(this.fieldset.tabs);
		},
		values() {
			return Object.assign({}, this.content);
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
	padding: var(--spacing-4) var(--spacing-3) var(--spacing-8);
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
