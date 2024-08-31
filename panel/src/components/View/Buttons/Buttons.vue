<template>
	<k-button-group v-if="buttons.length" class="k-view-buttons">
		<component
			:is="component(button)"
			v-for="button in buttons"
			:key="button.key"
			v-bind="button.props"
			@action="$emit('action', $event)"
		/>
	</k-button-group>
</template>

<script>
/**
 * Wrapper button group that dynamically renders the
 * respective view button components passed as `buttons` prop.
 *
 * @displayName ViewButtons
 * @since 5.0.0
 * @internal
 */
export default {
	props: {
		buttons: {
			type: Array,
			default: () => []
		}
	},
	emits: ["action"],
	methods: {
		component(button) {
			if (this.$helper.isComponent(button.component)) {
				return button.component;
			}

			return "k-view-button";
		}
	}
};
</script>
