<template>
	<k-form-dialog
		ref="dialog"
		v-bind="$props"
		class="k-page-create-dialog"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', value)"
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
			:value="value"
			@input="$emit('input', $event)"
			@submit="$emit('submit', $event)"
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
			default: () => window.panel.t("save")
		},
		template: {
			type: String
		}
	},
	emits: ["cancel", "input", "submit"],
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
			this.$panel.dialog.refresh({
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
</style>
