/**
 * The Batch Editing mixin is intended for all components
 * that want to introduce batch editing capabilities. It provides the
 * necessary methods and computed properties to handle item selection,
 * batch mode buttons and delete action.
 */
export default {
	data: () => ({
		isSelecting: false,
		selected: []
	}),
	created() {
		this.$events.on(this.batchEditingEvent, this.stopSelectingCollision);
	},
	destroyed() {
		this.$events.off(this.batchEditingEvent, this.stopSelectingCollision);
	},
	computed: {
		batchDeleteConfirmMessage() {
			return this.$t(`${this.type}.delete.confirm.selected`, {
				count: this.selected.length
			});
		},
		batchEditingButtons() {
			const buttons = [];

			buttons.push({
				disabled: this.selected.length === 0,
				icon: "trash",
				text: this.$t("delete") + ` (${this.selected.length})`,
				theme: "negative",
				click: () => {
					this.$panel.dialog.open({
						component: "k-remove-dialog",
						props: {
							text: this.batchDeleteConfirmMessage
						},
						on: {
							submit: async () => {
								this.$panel.dialog.close();

								if (this.selected.length === 0) {
									return;
								}

								await this.onBatchDelete();
								this.stopSelecting();
							}
						}
					});
				},
				responsive: true
			});

			buttons.push({
				icon: "cancel",
				text: this.$t("cancel"),
				click: () => this.onSelectToggle(),
				responsive: true
			});

			return buttons;
		},
		batchEditingEvent() {
			return "selecting";
		},
		batchEditingToggle() {
			return {
				icon: "checklist",
				click: () => this.onSelectToggle(),
				title: this.$t("select"),
				responsive: true
			};
		},
		canSelect() {
			return true;
		}
	},
	methods: {
		onBatchDelete() {
			throw new Error("Not implemented");
		},
		onSelect(ids) {
			this.selected = ids;
		},
		onSelectToggle() {
			this.isSelecting ? this.stopSelecting() : this.startSelecting();
		},
		startSelecting() {
			this.isSelecting = true;
			this.selected = [];
			this.$events.emit(this.batchEditingEvent, this.name);
		},
		stopSelecting() {
			this.isSelecting = false;
			this.selected = [];
		},
		stopSelectingCollision(name) {
			if (name !== this.name) {
				this.stopSelecting();
			}
		}
	}
};
