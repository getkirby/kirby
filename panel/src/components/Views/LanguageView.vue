<template>
	<k-inside>
		<k-view class="k-languages-view">
			<k-header :editable="true" @edit="update()">
				{{ name }}

				<k-button-group slot="left">
					<k-button :text="code.toUpperCase()" icon="globe" @click="update()" />
					<k-button
						:text="direction.toUpperCase()"
						:icon="direction === 'rtl' ? 'text-right' : 'text-left'"
						@click="update()"
					/>
					<k-button :text="$t('delete')" icon="trash" @click="remove()" />
				</k-button-group>
			</k-header>

			<k-bar>
				<k-headline slot="left">Translations</k-headline>
				<k-button-group slot="right">
					<k-button icon="add" :text="$t('add')" @click="createTranslation()" />
				</k-button-group>
			</k-bar>

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
				<k-empty @click="createTranslation">No translations yet</k-empty>
			</template>
		</k-view>
	</k-inside>
</template>

<script>
export default {
	props: {
		code: String,
		direction: String,
		id: String,
		name: String,
		translations: Array
	},
	methods: {
		createTranslation() {
			this.$dialog(`languages/${this.id}/translations/create`);
		},
		option({ option, row }) {
			this.$dialog(`languages/${this.id}/translations/${row.key}/${option}`);
		},
		remove() {
			this.$dialog(`languages/${this.id}/delete`);
		},
		update() {
			this.$dialog(`languages/${this.id}/update`);
		},
		updateTranslation({ row }) {
			this.$dialog(`languages/${this.id}/translations/${row.key}/update`);
		}
	}
};
</script>
