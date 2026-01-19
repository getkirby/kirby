<script>
import ModelPickerField from "./ModelPickerField.vue";

/**
 * @displayName FilesField
 */
export default {
	extends: ModelPickerField,
	type: "files",
	props: {
		uploads: [Boolean, Object, Array]
	},
	emits: ["change", "input"],
	computed: {
		buttons() {
			const buttons = ModelPickerField.computed.buttons.call(this);

			if (this.hasDropzone) {
				buttons.unshift({
					autofocus: this.autofocus,
					text: this.$t("upload"),
					responsive: true,
					icon: "upload",
					click: () => this.$panel.upload.pick(this.uploadOptions)
				});
			}

			return buttons;
		},
		emptyProps() {
			return {
				icon: "image",
				text:
					this.empty ??
					(this.multiple && this.max !== 1
						? this.$t("field.files.empty")
						: this.$t("field.files.empty.single"))
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
				preview: this.uploads.preview,
				url: this.$panel.urls.api + "/" + this.endpoints.field + "/upload",
				on: {
					done: async (files) => {
						if (this.multiple === false) {
							this.selected = [];
						}

						for (const file of files) {
							if (this.selected.findIndex((f) => this.isItem(f, file)) === -1) {
								this.selected.push(file);
							}
						}

						// send the input event
						// the content object gets updated
						const value = this.selected.map((file) => file.uuid ?? file.id);
						this.$emit("input", value);

						// the `$panel.content.update()` event sends
						// the updated form value object to the server
						await this.$panel.content.update();

						// if the picker dialog is still open, refresh it
						if (this.$panel.dialog.isOpen === true) {
							await this.$panel.dialog.refresh({
								query: {
									...this.$panel.dialog.query,
									value
								}
							});
						}
					}
				}
			};
		}
	},
	mounted() {
		this.$events.on("file.delete", this.removeById);
	},
	unmounted() {
		this.$events.off("file.delete", this.removeById);
	},
	methods: {
		drop(files) {
			if (this.uploads === false) {
				return false;
			}

			return this.$panel.upload.open(files, this.uploadOptions);
		}
	}
};
</script>
