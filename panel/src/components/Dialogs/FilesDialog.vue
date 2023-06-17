<template>
	<k-models-dialog
		ref="dialog"
		:empty="{
			icon: 'image',
			text: $t('dialog.files.empty')
		}"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
export default {
	methods: {
		open(models, options) {
			this.$refs.dialog.open(models, options);
		}
	}
};
</script>
