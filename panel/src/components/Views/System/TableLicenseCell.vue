<template>
	<k-url-field-preview
		v-if="value.link"
		:value="{ href: value.link, text: value.name }"
	/>
	<k-text-field-preview v-else :value="value.name" />
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
