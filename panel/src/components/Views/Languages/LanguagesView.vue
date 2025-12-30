<template>
	<k-panel-inside class="k-languages-view">
		<k-header>
			{{ $t("view.languages") }}

			<template #buttons>
				<k-view-buttons :buttons="buttons" />
			</template>
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
				<k-empty
					v-else
					icon="translate"
					:disabled="!$panel.permissions.languages.create"
					@click="$dialog('languages/create')"
				>
					{{ $t("languages.secondary.empty") }}
				</k-empty>
			</k-section>

			<k-section v-if="drafts.length" headline="Drafts">
				<k-collection :items="drafts" />
			</k-section>
		</template>

		<template v-else-if="languages.length === 0">
			<k-empty
				icon="translate"
				:disabled="!$panel.permissions.languages.create"
				@click="$dialog('languages/create')"
			>
				{{ $t("languages.empty") }}
			</k-empty>
		</template>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 */
export default {
	props: {
		buttons: Array,
		languages: {
			type: Array,
			default: () => []
		},
		variables: {
			type: Boolean,
			default: true
		}
	},
	computed: {
		drafts() {
			return this.languagesCollection.filter(
				(language) => language.status === "draft"
			);
		},
		languagesCollection() {
			return this.languages.map((language) => ({
				...language,
				link: this.variables
					? () => this.$go(`languages/${language.id}`)
					: null,
				options: [
					{
						icon: "edit",
						text: this.$t("edit"),
						disabled: this.variables === false,
						click: () => this.$go(`languages/${language.id}`)
					},
					{
						icon: "cog",
						text: this.$t("settings"),
						dialog: `languages/${language.id}/update`,
						disabled: !this.$panel.permissions.languages.update
					},
					{
						when: language.deletable,
						icon: "trash",
						text: this.$t("delete"),
						dialog: `languages/${language.id}/delete`,
						disabled: !this.$panel.permissions.languages.delete
					}
				]
			}));
		},
		primaryLanguage() {
			return this.languagesCollection.filter((language) => language.default);
		},
		secondaryLanguages() {
			return this.languagesCollection.filter(
				(language) => language.default === false && language.status !== "draft"
			);
		}
	}
};
</script>
