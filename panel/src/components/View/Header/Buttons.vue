<template>
	<k-button-group class="k-header-buttons">
		<component
			:is="`k-header-${button}-button`"
			v-for="button in buttons"
			:key="button"
		/>
	</k-button-group>
</template>

<script>
/**
 * Wrapper button group that dynamically renders the
 * respective header button components passed as `buttons` prop.
 * @internal
 */
export default {
	props: {
		buttons: Array
	}
};
</script>
