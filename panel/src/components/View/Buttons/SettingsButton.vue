<template>
	<k-view-button
		v-bind="$props"
		:disabled="$panel.content.isLocked()"
		@action="$emit('action', $event)"
	/>
</template>

<script>
import Button from "./Button.vue";

/**
 * View header button to open the model's settings dropdown
 * @displayName SettingsViewButton
 * @since 5.0.0
 * @deprecated 5.0.0
 * @internal
 * @todo implement fully on backend when changes feature implemented
 */
export default {
	extends: Button,
	emits: ["action"]
};
</script>
