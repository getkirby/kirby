<template>
	<component
		:is="component"
		v-if="$helper.isComponent(component)"
		:class="'k-section-name-' + name"
		:column="width"
		:content="formData"
		:lock="$panel.view.props.lock"
		:name="name"
		:parent="endpoints?.model ?? $panel.view.props.api"
		:timestamp="$panel.view.timestamp"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
	/>
	<k-box
		v-else
		:text="$t('error.section.type.invalid', { type: sectionType })"
		icon="alert"
		theme="negative"
	/>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		endpoints: Object,
		formData: Object,
		name: String,
		sectionType: String,
		width: String
	},
	emits: ["input", "submit"],
	computed: {
		component() {
			return "k-" + this.sectionType + "-section";
		}
	}
};
</script>
