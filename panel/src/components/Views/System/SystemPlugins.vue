<template>
	<k-section
		v-if="plugins.length"
		:headline="$t('plugins')"
		link="https://getkirby.com/plugins"
	>
		<k-table
			:index="false"
			:columns="{
				name: {
					label: $t('name'),
					type: 'url',
					mobile: true
				},
				author: {
					label: $t('author')
				},
				license: {
					label: $t('license'),
					type: 'license'
				},
				status: {
					label: $t('license.status'),
					type: 'license-status'
				},
				version: {
					label: $t('version'),
					type: 'update-status',
					mobile: true,
					width: '10rem'
				}
			}"
			:rows="plugins"
		/>
	</k-section>
</template>

<script>
/**
 * @internal
 * @since 4.0.0
 */
export default {
	props: {
		plugins: Array
	}
};
</script>
