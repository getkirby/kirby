<template>
	<k-bar class="k-registration" :data-is-local="$system.isLocal">
		<p>{{ $t("license.unregistered") }}</p>

		<k-button-group
			:buttons="[
				{
					icon: 'key',
					text: $t('license.register'),
					title: $t('license.unregistered'),
					size: 'sm',
					click: () => $dialog('registration')
				},
				{
					icon: 'cart',
					text: $t('license.buy'),
					link: 'https://getkirby.com/buy',
					size: 'sm',
					target: '_blank'
				}
			]"
			:responsive="true"
			size="sm"
		/>
	</k-bar>
</template>

<style>
.k-registration {
	height: var(--height-md);
	padding-inline-start: var(--spacing-3);
	background: var(--color-red-400);
	container-type: inline-size;
	margin-top: 1rem;
	margin-bottom: 1.5rem;
	color: var(--color-red-900);
	border-radius: var(--rounded);
	font-size: var(--text-xs);
}

.k-registration[data-is-local="true"] {
	color: var(--color-orange-900);
	background: var(--color-orange-400);
}

.k-registration .k-button-group {
	gap: 0;
}
.k-registration .k-button {
	--button-padding: var(--spacing-3);
}
</style>
