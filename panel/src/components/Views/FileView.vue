<template>
	<k-inside>
		<div
			:data-locked="isLocked"
			:data-id="model.id"
			:data-template="blueprint"
			class="k-file-view"
		>
			<k-file-preview v-bind="preview" :focusable="isFocusable" />
			<k-view class="k-file-content">
				<k-header
					:editable="permissions.changeName && !isLocked"
					:tab="tab.name"
					:tabs="tabs"
					@edit="$dialog(id + '/changeName')"
				>
					{{ model.filename }}
					<template #left>
						<k-button-group>
							<k-dropdown class="k-file-view-options">
								<k-button
									:disabled="isLocked"
									:responsive="true"
									:text="$t('settings')"
									icon="cog"
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
					</template>
					<template #right>
						<k-prev-next :prev="prev" :next="next" />
					</template>
				</k-header>
				<k-sections
					:blueprint="blueprint"
					:empty="$t('file.blueprint', { blueprint: $esc(blueprint) })"
					:lock="lock"
					:parent="id"
					:tab="tab"
				/>
			</k-view>
		</div>
		<template #footer>
			<k-form-buttons :lock="lock" />
		</template>
	</k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
	extends: ModelView,
	props: {
		preview: Object
	},
	computed: {
		isFocusable() {
			return (
				!this.isLocked &&
				this.permissions.update &&
				(!window.panel.multilang ||
					window.panel.languages.length === 0 ||
					window.panel.language.default)
			);
		}
	},
	methods: {
		action(action) {
			switch (action) {
				case "replace":
					return this.$panel.upload.replace(this.model, {
						on: { complete: this.onUpload }
					});
			}
		},
		onUpload() {
			this.$panel.notification.success();
			this.$reload();
		}
	}
};
</script>
