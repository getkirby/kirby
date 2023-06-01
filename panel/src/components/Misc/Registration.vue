<template>
	<k-bar class="k-registration" :data-is-local="$system.isLocal">
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
.k-registration {
	height: var(--height-xl);
	padding-inline: var(--main-padding-inline);
	background: var(--color-red-400);
	container-type: inline-size;
	margin-bottom: 1.5rem;
}

.k-registration[data-is-local="true"] {
	background: var(--color-orange-400);
}

.k-registration p {
	font-size: var(--text-sm);
}
</style>
