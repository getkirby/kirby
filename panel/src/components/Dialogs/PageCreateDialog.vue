<template>
	<k-form-dialog
		ref="dialog"
		v-bind="$props"
		class="k-page-create-dialog"
		@cancel="cancel"
		@submit="submit"
	>
		<k-select-field
			v-if="templates.length > 1"
			:empty="false"
			:label="$t('template')"
			:options="templates"
			:required="true"
			:value="template"
			class="k-page-template-switch"
			@input="pick($event)"
		/>

		<k-dialog-fields
			:fields="fields"
			:novalidate="novalidate"
			:value="value"
			@input="input"
			@submit="submit"
		/>
	</k-form-dialog>
</template>

<script>
import FormDialog from "./FormDialog.vue";

export default {
	mixins: [FormDialog],
	props: {
		blueprints: {
			type: Array
		},
		size: {
			default: "medium",
			type: String
		},
		submitButton: {
			type: [String, Boolean],
			default: () => window.panel.$t("save")
		},
		template: {
			type: String
		}
	},
	data() {
		return {
			model: this.value
		};
	},
	computed: {
		templates() {
			return this.blueprints.map((blueprint) => {
				return {
					text: blueprint.title,
					value: blueprint.name
				};
			});
		}
	},
	methods: {
		pick(template) {
			this.$panel.dialog.reload({
				query: {
					...this.$panel.dialog.query,
					slug: this.value.slug,
					template: template,
					title: this.value.title
				}
			});
		}
	}
};
</script>

<style>
.k-page-template-switch {
	margin-bottom: var(--spacing-6);
	padding-bottom: var(--spacing-6);
	border-bottom: 1px dashed var(--color-gray-300);
}
.k-page-template-switch nav {
	display: grid;
	grid-template-columns: 1fr 1fr;
	gap: 2px;
}
.k-page-template-switch .k-headline {
	margin-bottom: 0.75rem;
	line-height: 1.25;
}
.k-page-template-switch button {
	text-align: start;
	padding: 0.625rem 0.75rem;
	font-size: var(--text-sm);
	background: var(--color-white);
	border-radius: var(--rounded-sm);
	box-shadow: var(--shadow);
}
.k-page-template-switch button[aria-current] {
	background: var(--color-light);
}
</style>
