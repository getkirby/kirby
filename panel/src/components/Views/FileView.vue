<template>
	<k-inside
		:data-locked="isLocked"
		:data-id="model.id"
		:data-template="blueprint"
		class="k-file-view"
	>
		<template #topbar>
			<k-prev-next :prev="prev" :next="next" />
		</template>

		<k-file-preview v-bind="preview" />

		<k-header
			:editable="permissions.changeName && !isLocked"
			@edit="$dialog(id + '/changeName')"
		>
			{{ model.filename }}
			<template #buttons>
				<k-button-group>
					<k-button
						:link="preview.url"
						:responsive="true"
						:text="$t('open')"
						class="k-file-view-options"
						icon="open"
						size="sm"
						target="_blank"
						variant="filled"
					/>
					<k-dropdown class="k-file-view-options">
						<k-button
							:disabled="isLocked"
							:dropdown="true"
							:responsive="true"
							:text="$t('settings')"
							icon="cog"
							size="sm"
							variant="filled"
							@click="$refs.settings.toggle()"
						/>
						<k-dropdown-content
							ref="settings"
							:options="$dropdown(id)"
							@action="action"
						/>
					</k-dropdown>
					<k-languages-dropdown />
				</k-button-group>

				<k-form-buttons :lock="lock" />
			</template>
		</k-header>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:empty="$t('file.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="id"
			:tab="tab"
		/>

		<k-upload ref="upload" @success="onUpload" />
	</k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
	extends: ModelView,
	props: {
		preview: Object
	},
	methods: {
		action(action) {
			switch (action) {
				case "replace":
					this.$refs.upload.open({
						url: this.$urls.api + "/" + this.id,
						accept: "." + this.model.extension + "," + this.model.mime,
						multiple: false
					});
					break;
			}
		},
		onUpload() {
			this.$store.dispatch("notification/success", ":)");
			this.$reload();
		}
	}
};
</script>
