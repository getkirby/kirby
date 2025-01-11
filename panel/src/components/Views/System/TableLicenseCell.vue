<template>
	<div class="k-text-field-preview">
		<k-link v-if="value.link" :href="value.link" target="_blank">
			{{ value.name }}
		</k-link>
		<span v-else>{{ value.name }}</span>
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
