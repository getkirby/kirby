<template>
	<k-button-group class="k-view-buttons">
		<component
			:is="`k-view-${button}-button`"
			v-for="button in buttons"
			:key="button"
			@action="$emit('action', $event)"
		/>
	</k-button-group>
</template>

<script>
/**
 * Wrapper button group that dynamically renders the
 * respective view button components passed as `buttons` prop.
 * @internal
 */
export default {
	props: {
		buttons: Array
	},
	emits: ["action"]
};
</script>
