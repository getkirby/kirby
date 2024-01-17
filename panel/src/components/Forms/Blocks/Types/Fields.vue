<template>
	<div
		:data-collapsed="collapsed"
		@dblclick="!fieldset.wysiwyg ? $emit('open') : null"
	>
		<header class="k-block-type-fields-header">
			<k-block-title
				:content="values"
				:fieldset="fieldset"
				@click.native="toggle"
			/>
			<k-drawer-tabs
				v-if="!collapsed"
				:tab="tab"
				:tabs="fieldset.tabs"
				@open="tab = $event"
			/>
		</header>

		<k-form
			v-if="!collapsed"
			ref="form"
			:autofocus="true"
			:disabled="disabled || !fieldset.wysiwyg"
			:fields="fields"
			:value="values"
			class="k-block-type-fields-form"
			@input="$emit('update', $event)"
		/>
	</div>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeFields
 * @since 4.0.0
 */
export default {
	extends: Block,
	props: {
		endpoints: Object,
		tabs: Object
	},
	data() {
		return {
			collapsed: this.state(),
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
		},
		state(collapsed) {
			const id = `kirby.fieldsBlock.${this.endpoints.field}.${this.id}`;

			if (collapsed !== undefined) {
				sessionStorage.setItem(id, collapsed);
			} else {
				return JSON.parse(sessionStorage.getItem(id));
			}
		},
		toggle() {
			this.collapsed = !this.collapsed;
			this.state(this.collapsed);
		}
	}
};
</script>

<style>
/** TODO: .k-block-container:has(.k-block-type-fields) */
.k-block-container.k-block-container-type-fields {
	padding-block: 0;
}

/** TODO: .k-block-container:not([data-hidden="true"])
	.k-block-type-fields:has(.k-block-type-fields-form) */
.k-block-container:not([data-hidden="true"])
	.k-block-type-fields
	> :not([data-collapsed="true"]) {
	padding-bottom: var(--spacing-3);
}

.k-block-type-fields-header {
	display: flex;
	justify-content: space-between;
}
.k-block-type-fields-header .k-block-title {
	padding-block: var(--spacing-3);
	cursor: pointer;
}

.k-block-type-fields-form {
	background-color: var(--color-gray-200);
	padding: var(--spacing-6) var(--spacing-6) var(--spacing-8);
	border-radius: var(--rounded-sm);
	container: column / inline-size;
}
/** TODO: .k-block-container[data-hidden="true"]:has(.k-block-type-fields)
	:where(.k-drawer-tabs, .k-block-type-fields-form) */
.k-block-container-type-fields[data-hidden="true"]
	:where(.k-drawer-tabs, .k-block-type-fields-form) {
	display: none;
}
</style>
