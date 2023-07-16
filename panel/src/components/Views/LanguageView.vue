<template>
	<k-inside>
		<k-view class="k-language-view">
			<k-header :editable="true" @edit="update()">
				{{ name }}

				<k-button-group slot="left">
					<k-button :link="url" icon="open" text="Open" />
					<k-button :text="$t('settings')" icon="cog" @click="update()" />
					<k-button
						:disabled="!deletable"
						:text="$t('delete')"
						icon="trash"
						@click="remove()"
					/>
				</k-button-group>

				<template #right>
					<k-prev-next :prev="prev" :next="next" />
				</template>
			</k-header>

			<section>
				<k-bar>
					<k-headline slot="left" style="margin-bottom: var(--spacing-3)">{{ $t("language.settings") }}</k-headline>
				</k-bar>
				<k-stats :reports="info" size="small" />
			</section>

			<section>
				<k-bar>
					<k-headline slot="left">{{ $t("language.variables") }}</k-headline>
					<k-button-group slot="right">
						<k-button
							icon="add"
							:text="$t('add')"
							@click="createTranslation()"
						/>
					</k-button-group>
				</k-bar>

				<template v-if="translations.length">
					<k-table
						:columns="{
							key: {
								label: $t('language.variable.key'),
								mobile: true,
								width: '1/4'
							},
							value: {
								label: $t('language.variable.value'),
								mobile: true
							}
						}"
						:rows="translations"
						@cell="updateTranslation"
						@option="option"
					/>
				</template>
				<template v-else>
					<k-empty @click="createTranslation">{{ $t("language.variables.empty") }}</k-empty>
				</template>
			</section>
		</k-view>
	</k-inside>
</template>

<script>
export default {
	props: {
		code: String,
		deletable: Boolean,
		direction: String,
		id: String,
		info: Array,
		next: Object,
		name: String,
		prev: Object,
		translations: Array,
		url: String
	},
	methods: {
		createTranslation() {
			this.$panel.dialog.open(
				`dialogs/languages/${this.id}/translations/create`
			);
		},
		option(option, row) {
			// for the compatibility of the encoded url in different environments,
			// it is also encoded with base64 to ensure stable working
			this.$panel.dialog.open(
				`dialogs/languages/${this.id}/translations/${window.btoa(encodeURIComponent(row.key))}/${option}`
			);
		},
		remove() {
			this.$panel.dialog.open(`dialogs/languages/${this.id}/delete`);
		},
		update(focus) {
			this.$panel.dialog.open(`dialogs/languages/${this.id}/update`, {
				on: {
					ready: () => {
						this.$panel.dialog.focus(focus);
					}
				}
			});
		},
		updateTranslation({ row }) {
			// for the compatibility of the encoded url in different environments,
			// it is also encoded with base64 to ensure stable working
			this.$dialog(`languages/${this.id}/translations/${window.btoa(encodeURIComponent(row.key))}/update`);
		}
	}
};
</script>

<style>
.k-language-view section + section {
	margin-top: var(--spacing-6);
}
</style>
