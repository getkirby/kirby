<script>
import ModelsField from "./ModelsField.vue";

export default {
	extends: ModelsField,
	type: "files",
	props: {
		uploads: [Boolean, Object, Array]
	},
	computed: {
		emptyProps() {
			return {
				icon: "image",
				text: this.empty ?? this.$t("field.files.empty")
			};
		},
		hasDropzone() {
			return !this.disabled && this.more && this.uploads;
		},
		uploadOptions() {
			return {
				accept: this.uploads.accept,
				max: this.max,
				multiple: this.multiple,
				url: this.$panel.urls.api + "/" + this.endpoints.field + "/upload",
				on: {
					done: this.onUpload
				}
			};
		}
	},
	created() {
		this.$events.on("file.delete", this.removeById);
	},
	destroyed() {
		this.$events.off("file.delete", this.removeById);
	},
	methods: {
		drop(files) {
			if (this.uploads === false) {
				return false;
			}

			return this.$panel.upload.open(files, this.uploadOptions);
		},
		isSelected(file) {
			return this.selected.find((f) => f.id === file.id);
		},
		onUpload(files) {
			if (this.multiple === false) {
				this.selected = [];
			}

			for (const file of files) {
				if (this.isSelected(file) === false) {
					this.selected.push(file);
				}
			}

			this.onInput();
			this.$events.emit("model.update");
		}
	}
};
</script>
