<template>
	<div class="k-table-license-cell">
		<k-button
			:element="value.link ? 'k-link' : 'span'"
			:icon="value.status.icon"
			:link="value.link"
			:theme="value.status.theme"
			:title="value.status.label"
			size="xs"
			target="_blank"
		>
			{{ value.name }}
		</k-button>
	</div>
</template>

<script>
/**
 * @internal
 */
export default {
	inheritAttrs: false,
	props: {
		value: Object
	}
};
</script>

<style>
.k-table-license-cell {
	padding: 0 var(--spacing-1);
}
</style>
