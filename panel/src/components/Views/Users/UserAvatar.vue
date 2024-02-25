<template>
	<div>
		<k-button
			:title="$t('avatar')"
			variant="filled"
			class="k-user-view-image"
			@click="model.avatar ? $refs.avatar.toggle() : uploadAvatar()"
		>
			<k-image-frame v-if="model.avatar" :cover="true" :src="model.avatar" />
			<k-icon-frame v-else icon="user" />
		</k-button>
		<k-dropdown-content
			v-if="model.avatar"
			ref="avatar"
			:options="[
				{
					icon: 'upload',
					text: $t('change'),
					click: uploadAvatar
				},
				{
					icon: 'trash',
					text: $t('delete'),
					click: deleteAvatar
				}
			]"
		/>
	</div>
</template>

<script>
/**
 * @since 4.0.0
 * @internal
 */
export default {
	props: {
		model: Object
	},
	methods: {
		async deleteAvatar() {
			await this.$api.users.deleteAvatar(this.model.id);
			this.$panel.notification.success();
			this.$reload();
		},
		uploadAvatar() {
			this.$panel.upload.pick({
				url: this.$panel.urls.api + "/" + this.model.link + "/avatar",
				accept: "image/*",
				immediate: true,
				multiple: false
			});
		}
	}
};
</script>

<style>
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
.k-panel[data-theme="dark"] .k-user-view-image .k-icon-frame {
	--back: var(--color-gray-300);
	--icon-color: var(--color-gray-600);
}
</style>
