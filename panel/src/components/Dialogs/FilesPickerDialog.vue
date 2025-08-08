<template>
	<k-models-picker-dialog
		ref="dialog"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@drop="onDrop"
		@submit="$emit('submit', $event)"
	>
		<template #buttons>
			<k-button
				:title="$t('upload')"
				icon="upload"
				variant="filled"
				class="k-files-picker-dialog-button"
				@click="onUpload"
			/>
		</template>
	</k-models-picker-dialog>
</template>

<script>
import { props as ModelsPickerDialog } from "./ModelsPickerDialog.vue";

export default {
	mixins: [ModelsPickerDialog],
	props: {
		empty: {
			type: Object,
			default: () => ({
				icon: "image",
				text: window.panel.t("dialog.files.empty")
			})
		},
		uploads: Object
	},
	emits: ["cancel", "submit"],
	data() {
		return {
			uploaded: []
		};
	},
	computed: {
		uploadOptions() {
			return {
				...this.uploads,
				url: this.$panel.urls.api + "/" + this.uploads.url,
				on: {
					complete: (files) => {
						this.uploaded = files.map((file) => file.id);
					},
					closed: async () => {
						// give it a little time to have the thumbnail jobs ready
						await new Promise((resolve) => setTimeout(resolve, 500));

						this.$panel.dialog.refresh({
							query: {
								value: [...this.value, ...this.uploaded]
							}
						});
						this.uploaded = [];
					}
				}
			};
		}
	},
	methods: {
		onDrop(files) {
			this.$panel.upload.open(files, this.uploadOptions);
		},
		onUpload() {
			this.$panel.upload.pick(this.uploadOptions);
		}
	}
};
</script>

<style>
.k-files-picker-dialog-button {
	--button-height: var(--input-height);
	--button-width: var(--input-height);
}
</style>
