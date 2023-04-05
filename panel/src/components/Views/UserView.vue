<template>
	<k-inside
		:data-locked="isLocked"
		:data-id="model.id"
		:data-template="blueprint"
		class="k-user-view"
	>
		<template #topbar>
			<k-prev-next v-if="model.account" :prev="prev" :next="next" />
		</template>

		<div class="k-user-profile">
			<k-dropdown>
				<k-button
					:title="$t('avatar')"
					:aria-disabled="isLocked"
					variant="filled"
					class="k-user-view-image"
					@click="onAvatar"
				>
					<k-image-frame
						v-if="model.avatar"
						:cover="true"
						:src="model.avatar"
					/>
					<k-icon-frame v-else icon="user" />
				</k-button>
				<k-dropdown-content
					v-if="model.avatar"
					ref="picture"
					:options="avatarOptions"
				/>
			</k-dropdown>

			<k-button-group :buttons="buttons" />
		</div>

		<k-header
			:editable="permissions.changeName && !isLocked"
			@edit="$dialog(id + '/changeName')"
		>
			<span
				v-if="!model.name || model.name.length === 0"
				class="k-user-name-placeholder"
			>
				{{ $t("name") }} …
			</span>
			<template v-else>
				{{ model.name }}
			</template>

			<template #buttons>
				<k-button-group>
					<k-dropdown class="k-user-view-options">
						<k-button
							:disabled="isLocked"
							:dropdown="true"
							:text="$t('settings')"
							icon="cog"
							size="sm"
							variant="filled"
							@click="$refs.settings.toggle()"
						/>
						<k-dropdown-content ref="settings" :options="$dropdown(id)" />
					</k-dropdown>
					<k-languages-dropdown />
				</k-button-group>

				<k-form-buttons :lock="lock" />
			</template>
		</k-header>

		<k-model-tabs :tab="tab.name" :tabs="tabs" />

		<k-sections
			:blueprint="blueprint"
			:empty="$t('user.blueprint', { blueprint: $esc(blueprint) })"
			:lock="lock"
			:parent="id"
			:tab="tab"
		/>
		<k-upload
			ref="upload"
			:url="uploadApi"
			:multiple="false"
			accept="image/*"
			@success="uploadedAvatar"
		/>
	</k-inside>
</template>

<script>
import ModelView from "./ModelView.vue";

export default {
	extends: ModelView,
	computed: {
		avatarOptions() {
			return [
				{
					icon: "upload",
					text: this.$t("change"),
					click: () => this.$refs.upload.open()
				},
				{
					icon: "trash",
					text: this.$t("delete"),
					click: this.deleteAvatar
				}
			];
		},
		buttons() {
			return [
				{
					icon: "email",
					text: `${this.$t("email")}: ${this.model.email}`,
					disabled: !this.permissions.changeEmail || this.isLocked,
					click: () => this.$dialog(this.id + "/changeEmail")
				},
				{
					icon: "bolt",
					text: `${this.$t("role")}: ${this.model.role}`,
					disabled: !this.permissions.changeRole || this.isLocked,
					click: () => this.$dialog(this.id + "/changeRole")
				},
				{
					icon: "globe",
					text: `${this.$t("language")}: ${this.model.language}`,
					disabled: !this.permissions.changeLanguage || this.isLocked,
					click: () => this.$dialog(this.id + "/changeLanguage")
				}
			];
		},
		uploadApi() {
			return this.$urls.api + "/" + this.id + "/avatar";
		}
	},
	methods: {
		async deleteAvatar() {
			await this.$api.users.deleteAvatar(this.model.id);
			this.avatar = null;
			this.$panel.notification.success();
			this.$reload();
		},
		onAvatar() {
			if (this.model.avatar) {
				this.$refs.picture.toggle();
			} else {
				this.$refs.upload.open();
			}
		},
		uploadedAvatar() {
			this.$panel.notification.success();
			this.$reload();
		}
	}
};
</script>

<style>
.k-user-profile {
	--button-height: auto;
	padding: var(--spacing-2);
	background: var(--color-white);
	border-radius: var(--rounded-lg);
	display: flex;
	align-items: center;
	gap: var(--spacing-3);
	margin-bottom: var(--spacing-6);
}

.k-user-profile .k-button-group {
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}

.k-user-view-image {
	padding: 0;
}
.k-user-view-image .k-frame {
	width: 6rem;
	height: 6rem;
	border-radius: var(--rounded);
	line-height: 0;
}
.k-user-view-image .k-icon-frame {
	--back: var(--color-black);
	--icon-color: var(--color-gray-200);
}
.k-user-name-placeholder {
	color: var(--color-gray-500);
	transition: color 0.3s;
}
.k-header[data-editable="true"] .k-user-name-placeholder:hover {
	color: var(--color-gray-900);
}
</style>
