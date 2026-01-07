<template>
	<component
		:is="'k-' + sectionType + '-section'"
		:content="$panel.view.props.versions.changes"
		:name="name"
		:lock="$panel.view.props.lock"
		:parent="$panel.view.props.api"
		:timestamp="$panel.view.timestamp"
		:class="'k-section-name-' + name"
		@input="$emit('input', $event)"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
export default {
	props: {
		name: String,
		sectionType: String
	},
	emits: ["input", "submit"]
};
</script>
