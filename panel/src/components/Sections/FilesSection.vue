<script>
import ModelsSection from "@/components/Sections/ModelsSection.vue";

export default {
	extends: ModelsSection,
	computed: {
		addIcon() {
			return "upload";
		},
		canAdd() {
			return (
				this.$panel.permissions.files.create && this.options.upload !== false
			);
		},
		canDrop() {
			return this.canAdd !== false;
		},
		emptyProps() {
			return {
				icon: "image",
				text: this.$t("files.empty")
			};
		},
		items() {
			return this.data.map((file) => {
				file.sortable = this.options.sortable;
				file.column = this.column;
				file.options = this.$dropdown(file.link, {
					query: {
						view: "list",
						update: this.options.sortable,
						delete: this.data.length > this.options.min
					}
				});

				// add data-attributes info for item
				file.data = {
					"data-id": file.id,
					"data-template": file.template
				};

				return file;
			});
		},
		type() {
			return "files";
		},
		uploadOptions() {
			return {
				...this.options.upload,
				url: this.$panel.urls.api + "/" + this.options.upload.api,
				on: {
					complete: () => {
						this.$panel.notification.success({ context: "view" });
						this.$events.emit("file.upload");
					}
				}
			};
		}
	},
	mounted() {
		this.$events.on("model.update", this.reload);
		this.$events.on("file.sort", this.reload);
	},
	destroyed() {
		this.$events.off("model.update", this.reload);
		this.$events.off("file.sort", this.reload);
	},
	methods: {
		onAction(action, file) {
			if (action === "replace") {
				this.replace(file);
			}
		},
		onAdd() {
			if (this.canAdd) {
				this.$panel.upload.pick(this.uploadOptions);
			}
		},
		onDrop(files) {
			if (this.canAdd) {
				this.$panel.upload.open(files, this.uploadOptions);
			}
		},
		async onSort(items) {
			if (this.options.sortable === false) {
				return false;
			}

			this.isProcessing = true;

			try {
				await this.$api.patch(this.options.apiUrl + "/sort", {
					files: items.map((item) => item.id),
					index: this.pagination.offset
				});
				this.$panel.notification.success();
				this.$events.emit("file.sort");
			} catch (error) {
				this.$panel.error(error);
				this.reload();
			} finally {
				this.isProcessing = false;
			}
		},
		replace(file) {
			this.$panel.upload.replace(file, this.uploadOptions);
		}
	}
};
</script>
