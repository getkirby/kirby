<template>
	<k-section :headline="upgrade.headline" :link="upgrade.link">
		<k-items
			:items="
				upgrade.issues.map((issue) => ({
					theme: 'negative',
					image: {
						back: 'light-dark(var(--theme-color-200), var(--theme-color-900))',
						icon: issue.icon ?? 'alert',
						color: 'var(--theme-color-icon)'
					},
					target: '_blank',
					...issue
				}))
			"
		/>
	</k-section>
</template>

<script>
/**
 * @internal
 * @since 5.7.0
 */
export default {
	props: {
		upgrade: Object
	}
};
</script>
