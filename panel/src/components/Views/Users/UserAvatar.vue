<template>
	<k-button
		:disabled="isLocked"
		:title="$t('avatar')"
		class="k-user-view-image"
		@click="open"
	>
		<template v-if="avatar">
			<k-image-frame :cover="true" :src="avatar" />
			<k-dropdown-content
				ref="dropdown"
				:options="[
					{
						icon: 'upload',
						text: $t('change'),
						click: upload
					},
					{
						icon: 'trash',
						text: $t('delete'),
						click: remove
					}
				]"
			/>
		</template>
		<k-icon-frame v-else icon="user" />
	</k-button>
</template>

<script>
/**
 * @since 4.0.0
 * @unstable
 */
export default {
	props: {
		api: String,
		avatar: String,
		id: String,
		isLocked: Boolean
	},
	methods: {
		open() {
			if (this.avatar) {
				this.$refs.dropdown.toggle();
			} else {
				this.upload();
			}
		},
		async remove() {
			await this.$api.users.deleteAvatar(this.id);
			this.$panel.notification.success();
			this.$reload();
		},
		upload() {
			this.$panel.upload.pick({
				url: this.$panel.urls.api + "/" + this.api + "/avatar",
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
</style>
