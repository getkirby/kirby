<template>
	<k-text v-if="text" :html="text" />
	<k-box v-else theme="info">This dialog does not define any text</k-box>
</template>

<script>
export const props = {
	props: {
		text: String
	}
};

export default {
	mixins: [props]
};
</script>
