<template>
	<k-inside class="k-languages-view">
		<k-header>
			{{ $t("view.languages") }}

			<k-button-group slot="left">
				<k-button
					:text="$t('language.create')"
					icon="add"
					size="sm"
					variant="filled"
					@click="$dialog('languages/create')"
				/>
			</k-button-group>
		</k-header>

		<template v-if="languages.length > 0">
			<k-section :headline="$t('languages.default')">
				<k-collection :items="primaryLanguage" />
			</k-section>

			<k-section :headline="$t('languages.secondary')">
				<k-collection
					v-if="secondaryLanguages.length"
					:items="secondaryLanguages"
				/>
				<k-empty v-else icon="globe" @click="$dialog('languages/create')">
					{{ $t("languages.secondary.empty") }}
				</k-empty>
			</k-section>
		</template>

		<template v-else-if="languages.length === 0">
			<k-empty icon="globe" @click="$dialog('languages/create')">
				{{ $t("languages.empty") }}
			</k-empty>
		</template>
	</k-inside>
</template>

<script>
export default {
	props: {
		languages: {
			type: Array,
			default() {
				return [];
			}
		}
	},
	computed: {
		languagesCollection() {
			return this.languages.map((language) => ({
				...language,
				image: {
					back: "black",
					color: "gray",
					icon: "globe"
				},
				link: () => {
					this.$dialog(`languages/${language.id}/update`);
				},
				options: [
					{
						icon: "edit",
						text: this.$t("edit"),
						click() {
							this.$dialog(`languages/${language.id}/update`);
						}
					},
					{
						icon: "trash",
						text: this.$t("delete"),
						disabled: language.default && this.languages.length !== 1,
						click() {
							this.$dialog(`languages/${language.id}/delete`);
						}
					}
				]
			}));
		},
		primaryLanguage() {
			return this.languagesCollection.filter((language) => language.default);
		},
		secondaryLanguages() {
			return this.languagesCollection.filter(
				(language) => language.default === false
			);
		}
	}
};
</script>
