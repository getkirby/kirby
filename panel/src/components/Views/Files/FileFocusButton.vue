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
/**
 * @since 4.0.0
 * @internal
 */
export default {
	props: {
		focus: Object
	},
	emits: ["set"],
	methods: {
		set() {
			this.$emit("set", {
				x: 50,
				y: 50
			});
		},
		reset() {
			this.$emit("set", undefined);
		}
	}
};
</script>
