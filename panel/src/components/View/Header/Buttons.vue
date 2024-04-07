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
export default {
	props: {
		buttons: Array
	}
};
</script>
