<template>
	<k-panel-inside class="k-language-view">
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-header :editable="true" @edit="update()">
			{{ name }}
			<k-button-group slot="buttons">
				<k-button
					:link="url"
					:title="$t('open')"
					icon="open"
					size="sm"
					target="_blank"
					variant="filled"
				/>
				<k-button
					:title="$t('settings')"
					icon="cog"
					size="sm"
					variant="filled"
					@click="update()"
				/>
				<k-button
					:disabled="!deletable"
					:title="$t('delete')"
					icon="trash"
					size="sm"
					variant="filled"
					@click="remove()"
				/>
			</k-button-group>
		</k-header>

		<k-section headline="Language settings">
			<k-stats :reports="info" size="small" />
		</k-section>

		<k-section
			:buttons="[
				{
					click: createTranslation,
					icon: 'add',
					text: $t('add')
				}
			]"
			headline="Language variables"
		>
			<template v-if="translations.length">
				<k-table
					:columns="{
						key: {
							label: 'Key',
							mobile: true,
							width: '1/4'
						},
						value: {
							label: 'Value',
							mobile: true
						}
					}"
					:rows="translations"
					@cell="updateTranslation"
					@option="option"
				/>
			</template>
			<template v-else>
				<k-empty icon="globe" @click="createTranslation">
					No translations yet
				</k-empty>
			</template>
		</k-section>
	</k-panel-inside>
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
			this.$panel.dialog.open(
				`dialogs/languages/${this.id}/translations/${row.key}/${option}`
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
			this.$dialog(`languages/${this.id}/translations/${row.key}/update`);
		}
	}
};
</script>

<style>
.k-language-view section + section {
	margin-top: var(--spacing-6);
}
</style>
