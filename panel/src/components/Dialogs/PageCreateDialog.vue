<template>
	<k-form-dialog
		ref="dialog"
		v-bind="$props"
		class="k-page-create-dialog"
		@cancel="$emit('cancel')"
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
			ref="fields"
			:fields="fields"
			:value="value"
			@input="$emit('input', $event)"
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
			default: () => window.panel.t("save")
		},
		template: {
			type: String
		}
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
			this.$panel.dialog.refresh({
				query: {
					...this.$panel.dialog.query,
					slug: this.value.slug,
					template: template,
					title: this.value.title
				}
			});
		},
		submit() {
			// when novalidate is set on the form (for drafts),
			// manually validate title and slug
			if (this.novalidate === true) {
				const fields = this.$refs.fields.$el;
				const title = fields.querySelector('input[name="title"]');
				const slug = fields.querySelector('input[name="slug"]');

				// check title & slug validity and show native validation message
				if (title?.checkValidity() === false) {
					return title.reportValidity();
				}
				if (slug?.checkValidity() === false) {
					return slug.reportValidity();
				}
			}

			this.$emit("submit", this.value);
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
