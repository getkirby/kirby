<template>
	<p class="k-url-field-preview">
		<k-link :to="url" @click.native.stop>
			{{ version }}
		</k-link>

		<span
			v-if="hasInfo"
			class="k-table-update-status-cell-info"
			:data-theme="value.theme"
		>
			{{ value.label }}
		</span>
	</p>
</template>

<script>
export default {
	props: {
		value: [String, Object]
	},
	computed: {
		hasInfo() {
			return typeof this.value !== "string";
		},
		url() {
			return this.hasInfo ? this.value.url : null;
		},
		version() {
			return this.hasInfo ? this.value.version : this.value;
		}
	}
};
</script>

<style>
.k-table-update-status-cell-info {
	color: var(--theme, var(--color-gray-500));
	display: block;
}
</style>
