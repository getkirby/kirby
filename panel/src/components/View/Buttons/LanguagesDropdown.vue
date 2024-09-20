<template>
	<k-view-button v-bind="$props" :options="languages" />
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
		options: {
			type: Array,
			default: () => []
		}
	},
	computed: {
		languages() {
			return this.options.map((option) => {
				if (option === "-") {
					return option;
				}

				return {
					...option,
					click: () => this.change(option)
				};
			});
		}
	},
	methods: {
		change(language) {
			this.$reload({
				query: {
					language: language.code
				}
			});
		}
	}
};
</script>
