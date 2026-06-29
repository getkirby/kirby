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
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @internal
 */
export default {
	props: {
		plugins: Array
	}
};
</script>
