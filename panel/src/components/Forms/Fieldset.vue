<template>
	<div class="k-fieldset">
		<k-grid variant="fields">
			<template v-for="(field, fieldName) in fields">
				<k-column
					v-if="$helper.field.isVisible(field, value)"
					:key="field.signature"
					:width="field.width"
				>
					<!-- @event input Triggered whenever any field value changes -->
					<!-- @event focus Triggered whenever any field is focused -->
					<!-- @event submit Triggered whenever any field triggers submit -->
					<!-- eslint-disable vue/no-mutating-props -->
					<component
						:is="'k-' + field.type + '-field'"
						v-if="hasFieldType(field.type)"
						:ref="fieldName"
						v-bind="field"
						:disabled="disabled || field.disabled"
						:form-data="value"
						:name="fieldName"
						:novalidate="novalidate"
						:value="value[fieldName]"
						@input="onInput($event, field, fieldName)"
						@focus="$emit('focus', $event, field, fieldName)"
						@submit="$emit('submit', $event, field, fieldName)"
					/>
					<k-box v-else theme="negative">
						<k-text size="small">
							{{
								$t("error.field.type.missing", {
									name: fieldName,
									type: field.type
								})
							}}
						</k-text>
					</k-box>
				</k-column>
			</template>
		</k-grid>
	</div>
</template>

<script>
/**
 * The Fieldset component is a wrapper around manual field component creation. You simply pass it an fields object and all field components will automatically be created including a nice field grid. This is the ideal starting point if you want an easy way to create fields without having to deal with a full form element.
 */
export default {
	props: {
		/**
		 * @private
		 */
		config: Object,
		/**
		 * If `true`, all fields in the fieldset are disabled
		 */
		disabled: Boolean,
		/**
		 * Object with field definitions. Check out the field components
		 * for available props
		 */
		fields: {
			type: [Array, Object],
			default: () => ({})
		},
		/**
		 * If `true`, form fields won't show their validation status on the fly.
		 */
		novalidate: {
			type: Boolean,
			default: false
		},
		/**
		 * Key/Value object with all values for all fields
		 */
		value: {
			type: Object,
			default: () => ({})
		}
	},
	emits: ["focus", "input", "submit"],
	methods: {
		/**
		 * Focus a specific field in the fieldset or the first one if no name is given
		 * @public
		 * @param  {string} name field name to focus
		 */
		focus(name) {
			if (name) {
				if (
					this.hasField(name) &&
					typeof this.$refs[name][0].focus === "function"
				) {
					this.$refs[name][0].focus();
				}
				return;
			}

			const key = Object.keys(this.$refs)[0];
			this.focus(key);
		},
		/**
		 * Check if a particular field type exists
		 * @public
		 * @param {string} type field type
		 */
		hasFieldType(type) {
			return this.$helper.isComponent(`k-${type}-field`);
		},
		/**
		 * Check if a field with the given name exists in the fieldset
		 * @public
		 * @param {string} name field name
		 */
		hasField(name) {
			return this.$refs[name]?.[0];
		},
		onInput(value, field, name) {
			const values = this.value;
			this.$set(values, name, value);
			this.$emit("input", values, field, name);
		},
		hasErrors() {
			// TODO: refactor using native invalid states (or check if can be removed completely)
			return false;
		}
	}
};
</script>

<style>
.k-fieldset {
	border: 0;
}
</style>
