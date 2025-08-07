<template>
	<k-dialog
		ref="dialog"
		class="k-upload-dialog"
		v-bind="$props"
		:disabled="disabled || $panel.upload.files.length === 0"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-dropzone @drop="$panel.upload.select($event)">
			<!-- No files yet -->
			<k-empty
				v-if="$panel.upload.files.length === 0"
				icon="upload"
				layout="cards"
				@click="$panel.upload.pick()"
			>
				{{ $t("files.empty") }}
			</k-empty>

			<!-- Files list -->
			<k-upload-items
				v-else
				:items="$panel.upload.files"
				@remove="
					(file) => {
						$panel.upload.remove(file.id);
					}
				"
				@rename="
					(file, name) => {
						file.name = name;
					}
				"
			/>
		</k-dropzone>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * @since 4.0.0
 */
export default {
	mixins: [Dialog],
	props: {
		submitButton: {
			type: [String, Boolean, Object],
			default: () => {
				return {
					icon: "upload",
					text: window.panel.t("upload")
				};
			}
		}
	},
	emits: ["cancel", "submit"]
};
</script>

<style>
.k-upload-dialog.k-dialog {
	--dialog-width: 40rem;
}
</style>
