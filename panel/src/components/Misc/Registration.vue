<template>
	<k-box :theme="$system.isLocal ? 'warning' : 'negative'">
		<p>{{ $t("license.unregistered") }}</p>

		<k-button-group
			:buttons="[
				{
					icon: 'key',
					title: $t('license.register'),
					size: 'sm',
					click: () => $dialog('registration')
				},
				{
					icon: 'cart',
					title: $t('license.buy'),
					link: 'https://getkirby.com/buy',
					size: 'sm',
					target: '_blank'
				}
			]"
			:responsive="true"
			size="sm"
		/>
	</k-box>
</template>

<style scoped>
.k-box {
	color: var(--theme-color-900);
}

.k-box .k-button-group {
	gap: 0;
}
.k-box .k-button {
	--button-padding: var(--spacing-3);
}
</style>
