<script>
import ModelsSection from "@/components/Sections/ModelsSection.vue";

export default {
	extends: ModelsSection,
	computed: {
		canAdd() {
			return this.options.add && this.$panel.permissions.pages.create;
		},
		items() {
			return this.data.map((page) => {
				const sortable =
					page.permissions.sort && this.options.sortable && !this.isSelecting;
				const deletable =
					page.permissions.delete && this.data.length > this.options.min;

				const flag = {
					...this.$helper.page.status(
						page.status,
						page.permissions.changeStatus === false
					),
					class: "k-page-status-icon-option",
					dialog: page.link + "/changeStatus"
				};

				return {
					...page,
					buttons: [flag, ...(page.buttons ?? [])],
					column: this.column,
					data: {
						"data-id": page.id,
						"data-status": page.status,
						"data-template": page.template
					},
					// TODO: remove `flag` once table layout has been refactored
					// into a separate component and `buttons` support has been added
					flag,
					deletable,
					options: this.$dropdown(page.link, {
						query: {
							view: "list",
							delete: deletable,
							sort: sortable
						}
					}),
					selectable: this.isSelecting && deletable,
					sortable
				};
			});
		},
		type() {
			return "pages";
		}
	},
	mounted() {
		this.$events.on("page.changeStatus", this.reload);
		this.$events.on("page.sort", this.reload);
	},
	unmounted() {
		this.$events.off("page.changeStatus", this.reload);
		this.$events.off("page.sort", this.reload);
	},
	methods: {
		onAdd() {
			if (this.canAdd) {
				this.$panel.dialog.open(
					this.parent + "/sections/" + this.name + "/create"
				);
			}
		},
		async onChange(event) {
			let type = null;

			if (event.added) {
				type = "added";
			}

			if (event.moved) {
				type = "moved";
			}

			if (type) {
				this.isProcessing = true;

				const element = event[type].element;
				const position = event[type].newIndex + 1 + this.pagination.offset;

				try {
					await this.$api.pages.changeStatus(element.id, "listed", position);
					this.$panel.notification.success();
					this.$events.emit("page.sort", element);
				} catch (error) {
					this.$panel.error({
						message: error.message,
						details: error.details
					});

					await this.reload();
				} finally {
					this.isProcessing = false;
				}
			}
		}
	}
};
</script>
