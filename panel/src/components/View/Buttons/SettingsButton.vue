<template>
	<div>
		<k-button
			:disabled="$panel.content.isLocked"
			:dropdown="true"
			:title="$t('settings')"
			icon="cog"
			variant="filled"
			size="sm"
			class="k-view-settings-button k-view-options"
			@click="onClick"
		/>
		<k-dropdown-content
			v-if="dropdown"
			ref="dropdown"
			:options="$dropdown(dropdown)"
			align-x="end"
			@action="$emit('action', $event)"
		/>
	</div>
</template>

<script>
/**
 * View header button to open the model's settings dropdown
 * @since 5.0.0
 */
export default {
	inheritAttrs: false,
	emits: ["action"],
	computed: {
		dropdown() {
			return this.model?.link;
		},
		model() {
			return this.$panel.view.props.model;
		}
	},
	methods: {
		onClick() {
			if (this.dropdown) {
				return this.$refs.dropdown.toggle();
			}

			this.$emit("action", "settings");
		}
	}
};
</script>
