<template>
	<nav v-if="buttons.length" class="k-view-buttons">
		<k-button-group v-for="(group, index) in groups" :key="index">
			<component
				:is="component(button)"
				v-for="button in group"
				:key="button.key"
				v-bind="button.props"
				@action="$emit('action', $event)"
			/>
		</k-button-group>
	</nav>
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
	computed: {
		groups() {
			return this.$helper.array.split(this.buttons, "-");
		}
	},
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

<style>
.k-view-buttons {
	display: flex;
	gap: var(--spacing-3);
}
</style>
