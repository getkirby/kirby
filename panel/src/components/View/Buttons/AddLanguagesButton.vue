<template>
	<k-view-button
		:text="$t('language.create')"
		icon="add"
		@click="$dialog('languages/create')"
	/>
</template>
