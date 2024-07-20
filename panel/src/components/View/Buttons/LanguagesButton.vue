<template>
	<k-view-button
		:options="options"
		:text="code"
		icon="translate"
		responsive="text"
		class="k-view-languages-button k-languages-dropdown"
	/>
</template>

<script>
/**
 * View header button to switch between content languages
 * @displayName ViewLanguagesButton
 * @since 4.0.0
 * @internal
 */
export default {
	computed: {
		code() {
			return this.language.code.toUpperCase();
		},
		language() {
			return this.$panel.language;
		},
		languages() {
			return this.$panel.languages;
		},
		options() {
			const options = [];

			// add the primary/default language first
			const primaryLanguage = this.languages.find(
				(language) => language.default === true
			);

			options.push(this.item(primaryLanguage));
			options.push("-");

			// add all secondary languages after the separator
			const secondaryLanguages = this.languages.filter(
				(language) => language.default === false
			);

			for (const language of secondaryLanguages) {
				options.push(this.item(language));
			}

			return options;
		}
	},
	methods: {
		change(language) {
			this.$reload({
				query: {
					language: language.code
				}
			});
		},
		item(language) {
			return {
				click: () => this.change(language),
				current: language.code === this.language.code,
				text: language.name
			};
		}
	}
};
</script>
