<template>
	<k-models-dialog
		ref="dialog"
		:empty="{
			icon: 'users',
			text: $t('dialog.users.empty')
		}"
		:item="item"
		@cancel="$emit('cancel')"
		@submit="$emit('submit', $event)"
	/>
</template>

<script>
export default {
	methods: {
		item(item) {
			return {
				...item,
				key: item.email,
				info: item.info !== item.text ? item.info : null
			};
		},
		open(models, options) {
			this.$refs.dialog.open(models, options);
		}
	}
};
</script>
