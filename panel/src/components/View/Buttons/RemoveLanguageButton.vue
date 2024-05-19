<template>
	<k-button
		v-if="
			$panel.view.component === 'k-language-view' && $panel.view.props.deletable
		"
		:title="$t('delete')"
		icon="trash"
		size="sm"
		variant="filled"
		@click="$dialog(`languages/${$panel.view.props.id}/delete`)"
	/>
</template>
