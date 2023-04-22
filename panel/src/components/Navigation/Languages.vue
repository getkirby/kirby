<template>
	<k-dropdown v-if="languages.length" class="k-languages-dropdown">
		<k-button
			:dropdown="true"
			:responsive="true"
			:text="code"
			icon="globe"
			size="sm"
			variant="filled"
			@click="$refs.languages.toggle()"
		/>
		<k-dropdown-content v-if="languages" ref="languages">
			<k-dropdown-item @click="change(defaultLanguage)">
				{{ defaultLanguage.name }}
			</k-dropdown-item>
			<hr />
			<k-dropdown-item
				v-for="languageItem in languages"
				:key="languageItem.code"
				@click="change(languageItem)"
			>
				{{ languageItem.name }}
			</k-dropdown-item>
		</k-dropdown-content>
	</k-dropdown>
</template>

<script>
export default {
	computed: {
		code() {
			return this.language.code.toUpperCase();
		},
		defaultLanguage() {
			return this.$panel.languages.find(
				(language) => language.default === true
			);
		},
		language() {
			return this.$panel.language;
		},
		languages() {
			return this.$panel.languages.filter(
				(language) => language.default === false
			);
		}
	},
	methods: {
		change(language) {
			this.$emit("change", language);
			this.$go(window.location, {
				query: {
					language: language.code
				}
			});
		}
	}
};
</script>
