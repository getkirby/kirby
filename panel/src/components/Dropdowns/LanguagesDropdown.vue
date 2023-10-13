<template>
	<div v-if="languages.length > 1" class="k-languages-dropdown">
		<k-button
			:dropdown="true"
			:text="code"
			icon="translate"
			responsive="text"
			size="sm"
			variant="filled"
			@click="$refs.languages.toggle()"
		/>
		<k-dropdown-content ref="languages" :options="options" />
	</div>
</template>

<script>
/**
 * @since 4.0.0
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
