<template>
	<k-button
		:icon="focus ? 'cancel-small' : 'preview'"
		:title="focus ? $t('file.focus.reset') : undefined"
		size="xs"
		variant="filled"
		@click="focus ? reset() : set()"
	>
		<template v-if="focus">{{ focus.x }}% {{ focus.y }}%</template>
		<template v-else>{{ $t("file.focus.placeholder") }}</template>
	</k-button>
</template>

<script>
export default {
	props: {
		file: Object,
		focus: Object
	},
	methods: {
		set() {
			this.$emit("set", "50% 50%");
		},
		reset() {
			this.$emit("set", undefined);
		}
	}
};
</script>
