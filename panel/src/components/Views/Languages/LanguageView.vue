<template>
	<k-panel-inside class="k-language-view">
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-header :editable="canUpdate" @edit="update()">
			{{ name }}

			<template #buttons>
				<k-button-group>
					<k-button
						:link="url"
						:title="$t('open')"
						icon="open"
						size="sm"
						target="_blank"
						variant="filled"
					/>
					<k-button
						:disabled="!canUpdate"
						:title="$t('settings')"
						icon="cog"
						size="sm"
						variant="filled"
						@click="update()"
					/>
					<k-button
						v-if="deletable"
						:disabled="!$panel.permissions.languages.delete"
						:title="$t('delete')"
						icon="trash"
						size="sm"
						variant="filled"
						@click="remove()"
					/>
				</k-button-group>
			</template>
		</k-header>

		<k-section :headline="$t('language.settings')">
			<k-stats :reports="info" size="small" />
		</k-section>

		<k-section
			:buttons="[
				/**
				 * @todo update disabled prop when new `languageVariables.*` permissions available
				 */
				{
					click: createTranslation,
					disabled: !canUpdate,
					icon: 'add',
					text: $t('add')
				}
			]"
			:headline="$t('language.variables')"
		>
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
					:disabled="!canUpdate"
					:rows="translations"
					@cell="updateTranslation"
					@option="option"
				/>
			</template>
			<template v-else>
				<k-empty :disabled="!canUpdate" icon="translate" @click="createTranslation">
					{{ $t("language.variables.empty") }}
				</k-empty>
			</template>
		</k-section>
	</k-panel-inside>
</template>

<script>
/**
 * @internal
 * @since 4.0.0
 */
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
	computed: {
		canUpdate() {
			return this.$panel.permissions.languages.update;
		}
	},
	methods: {
		createTranslation() {
			if (!this.canUpdate) {
				return;
			}

			this.$dialog(`languages/${this.id}/translations/create`);
		},
		option(option, row) {
			if (!this.canUpdate) {
				return;
			}

			// for the compatibility of the encoded url in different environments,
			// it is also encoded with base64 to reduce special characters
			this.$dialog(
				`languages/${this.id}/translations/${window.btoa(
					encodeURIComponent(row.key)
				)}/${option}`
			);
		},
		remove() {
			this.$dialog(`languages/${this.id}/delete`);
		},
		update(focus) {
			this.$dialog(`languages/${this.id}/update`, {
				on: {
					ready: () => {
						this.$panel.dialog.focus(focus);
					}
				}
			});
		},
		updateTranslation({ row }) {
			if (!this.canUpdate) {
				return;
			}

			// for the compatibility of the encoded url in different environments,
			// it is also encoded with base64 to reduce special characters
			this.$dialog(
				`languages/${this.id}/translations/${window.btoa(
					encodeURIComponent(row.key)
				)}/update`
			);
		}
	}
};
</script>
