<template>
	<k-view-button v-bind="$props" :options="options" />
</template>

<script>
import { props as ButtonProps } from "@/components/Navigation/Button.vue";

/**
 * View header button to switch between content languages
 * @displayName LanguagesViewButton
 * @since 4.0.0
 * @internal
 */
export default {
	mixins: [ButtonProps],
	props: {
		options: String
	}
};
</script>
