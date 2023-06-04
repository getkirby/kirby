<template>
	<k-bar :data-is-local="$system.isLocal">
		<p>{{ $t("license.unregistered") }}</p>

		<k-button-group
			:buttons="[
				{
					icon: 'key',
					text: $t('license.register'),
					title: $t('license.unregistered'),
					click: () => $dialog('registration')
				},
				{
					icon: 'cart',
					text: $t('license.buy'),
					link: 'https://getkirby.com/buy',
					target: '_blank'
				}
			]"
			:responsive="true"
			size="sm"
		/>
	</k-bar>
</template>

<style scoped>
.k-bar {
	position: sticky;
	bottom: 0;
	height: var(--height-xl);
	padding-inline: var(--main-padding-inline);
	background: var(--color-red-500);
	container-type: inline-size;
}

.k-bar[data-is-local="true"] {
	background: var(--color-blue-300);
}

.k-bar p {
	font-size: var(--text-sm);
}
</style>
