<script>
import ModelsField from "./ModelsField.vue";

/**
 * @displayName PagessectionField
 * @since 6.0.0
 */
export default {
	extends: ModelsField,
	type: "pages",
	props: {
		add: Boolean
	},
	computed: {
		canAdd() {
			return this.add && this.$panel.permissions.pages.create;
		},
		items() {
			return this.models.map((page) => {
				const sortable =
					page.permissions.sort && this.sortable && !this.isSelecting;
				const deletable =
					page.permissions.delete && this.models.length > this.min;

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
					// column: this.column,
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
		}
	},
	methods: {
		onAdd() {
			if (this.canAdd) {
				this.$panel.dialog.open(this.endpoints.field + "/create");
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

					this.$panel.view.reload();
				}
			}
		}
	}
};
</script>
