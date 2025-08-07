<template>
	<k-dialog
		ref="dialog"
		class="k-upload-dialog k-upload-replace-dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<ul class="k-upload-items">
			<li class="k-upload-original">
				<k-upload-item-preview
					:color="original.image?.color"
					:icon="original.image?.icon"
					:url="original.url"
					:type="original.mime"
				/>
			</li>

			<li>&larr;</li>

			<k-upload-item
				v-bind="file"
				:color="original.image?.color"
				:editable="false"
				:icon="original.image?.icon"
				:name="$helper.file.name(original.filename)"
				:removable="false"
			/>
		</ul>
	</k-dialog>
</template>

<script>
import UploadDialog from "./UploadDialog.vue";

/**
 * @since 4.0.0
 */
export default {
	extends: UploadDialog,
	props: {
		original: Object,
		submitButton: {
			type: [String, Boolean, Object],
			default: () => {
				return {
					icon: "upload",
					text: window.panel.t("replace")
				};
			}
		}
	},
	emits: ["cancel", "submit"],
	computed: {
		file() {
			return this.$panel.upload.files[0];
		}
	}
};
</script>

<style>
.k-upload-replace-dialog .k-upload-items {
	display: flex;
	gap: var(--spacing-3);
	align-items: center;
}

.k-upload-original {
	width: 6rem;
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	overflow: hidden;
}

.k-upload-replace-dialog .k-upload-item {
	flex-grow: 1;
}
</style>
