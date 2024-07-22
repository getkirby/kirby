<template>
	<k-view-button
		v-if="
			$panel.view.component === 'k-language-view' && $panel.view.props.deletable
		"
		:title="$t('delete')"
		icon="trash"
		@click="$dialog(`languages/${$panel.view.props.id}/delete`)"
	/>
</template>
