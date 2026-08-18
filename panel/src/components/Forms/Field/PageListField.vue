<script>
import ModelListField from "@/components/Forms/Field/ModelListField.vue";

/**
 * Displays a list of pages
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	extends: ModelListField,
	type: "pages",
	computed: {
		canAdd() {
			return this.state.add && this.$panel.permissions.pages.create;
		},
		icon() {
			return "page";
		},
		items() {
			return this.state.models.map((page) => {
				const sortable = page.permissions.sort && this.isSortable;
				const deletable =
					page.permissions.delete &&
					this.state.pagination.total > (this.min ?? 0);

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
					data: {
						"data-id": page.id,
						"data-status": page.status,
						"data-template": page.template
					},
					// TODO: remove `flag` once the table layout has been refactored
					// into a separate component and `buttons` support has been added
					flag,
					deletable,
					options: this.$dropdown(page.link, {
						query: {
							delete: deletable,
							sort: sortable,
							view: "list"
						}
					}),
					selectable: this.isSelecting && deletable,
					sortable: sortable
				};
			});
		}
	},
	methods: {
		onAdd() {
			if (this.canAdd === true) {
				this.$panel.dialog.open(this.endpoints.field + "/create");
			}
		},
		/**
		 * Pages carry their position in the status, so sorting
		 * them means moving them to a new listed position
		 */
		async onChange(event) {
			const type = event.added ? "added" : event.moved ? "moved" : null;

			if (type === null) {
				return;
			}

			const element = event[type].element;
			const position = event[type].newIndex + 1 + this.state.pagination.offset;

			await this.request(() =>
				this.$api.pages.changeStatus(element.id, "listed", position)
			);
		},
		refreshEvents() {
			return ["model.update", "page.changeStatus", "page.sort"];
		}
	}
};
</script>
