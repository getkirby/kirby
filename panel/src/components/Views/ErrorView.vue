<template>
	<component :is="`k-panel-${layout}`" class="k-error-view">
		<template v-if="layout === 'outside'">
			<div>
				<k-box :icon="{ type: 'alert' }" theme="negative">{{ error }}</k-box>
			</div>
		</template>
		<template v-else>
			<k-header>{{ $t("error") }}</k-header>
			<k-box :icon="{ type: 'alert' }" theme="negative">{{ error }}</k-box>
		</template>
	</component>
</template>

<script>
export default {
	props: {
		error: String,
		layout: String
	}
};
</script>
