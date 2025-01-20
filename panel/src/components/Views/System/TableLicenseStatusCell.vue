<template>
	<div class="k-table-license-status-cell">
		<k-button
			:dialog="value.dialog"
			:drawer="value.drawer"
			:element="element"
			:icon="value.icon"
			:link="value.link"
			:theme="value.theme"
			size="xs"
			target="_blank"
		>
			{{ value.label }}
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
	},
	computed: {
		element() {
			if (this.value.dialog || this.value.drawer || this.value.link) {
				// allow using the computed component from `k-button`
				return null;
			}

			// force a `<span>` element instead of a `<button>`
			return "span";
		}
	}
};
</script>

<style>
.k-table-license-status-cell {
	padding: 0 var(--spacing-1);
}
</style>
