<template>
	<k-box :theme="$system.isLocal ? 'warning' : 'negative'">
		<p>{{ $t("license.unregistered") }}</p>

		<k-button-group
			:buttons="[
				{
					click: () => $dialog('registration'),
					icon: 'key',
					responsive: true,
					size: 'sm',
					text: $t('license.register')
				},
				{
					icon: 'cart',
					link: 'https://getkirby.com/buy',
					responsive: true,
					size: 'sm',
					target: '_blank',
					text: $t('license.buy')
				}
			]"
			size="sm"
		/>
	</k-box>
</template>

<style scoped>
.k-box {
	--box-height: var(--height);
	color: var(--theme-color-900);
	padding: 0 var(--spacing-3);
	border-radius: 0;
	border-bottom: 1px solid var(--theme-color-500);
	justify-content: space-between;
	container-type: inline-size;
}
.k-box p {
	padding-inline: var(--spacing-2);
}
</style>
