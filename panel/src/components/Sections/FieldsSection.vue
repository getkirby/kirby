<template>
	<k-section :class="['k-fields-section', $attrs.class]" :style="$attrs.style">
		<k-form
			:fields="fieldset"
			:validate="true"
			:value="content"
			:disabled="lock && lock.state === 'lock'"
			@input="$emit('input', $event)"
			@submit="$emit('submit', $event)"
		/>
	</k-section>
</template>

<script>
import SectionMixin from "@/mixins/section.js";

export default {
	mixins: [SectionMixin],
	inheritAttrs: false,
	props: {
		content: Object,
		fields: Object,
		props: Object
	},
	emits: ["input", "submit"],
	computed: {
		fieldset() {
			const fields = {};
			const fieldNames = Object.keys(this.props.fields).map((name) =>
				name.toLowerCase()
			);

			for (const fieldName of fieldNames) {
				if (!this.fields[fieldName]) {
					continue;
				}

				fields[fieldName] = {
					...this.fields[fieldName],
					section: this.name,
					endpoints: {
						field: this.parent + "/fields/" + fieldName,
						section: this.parent + "/sections/" + this.name,
						model: this.parent
					}
				};
			}

			return fields;
		}
	}
};
</script>

<style>
.k-fields-section input[type="submit"] {
	display: none;
}

[data-locked="true"] .k-fields-section {
	opacity: 0.2;
	pointer-events: none;
}
</style>
