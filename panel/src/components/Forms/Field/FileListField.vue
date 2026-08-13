<script>
import ModelListField from "@/components/Forms/Field/ModelListField.vue";

/**
 * Displays a list of files
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	extends: ModelListField,
	props: {
		/**
		 * Upload settings or `false` if uploads are disabled
		 */
		upload: {
			type: [Boolean, Object],
			default: false
		}
	},
	computed: {
		addButton() {
			return {
				icon: "upload",
				text: this.$t("add"),
				click: () => this.onAdd(),
				responsive: true
			};
		},
		canAdd() {
			return Boolean(this.state.upload) && this.$panel.permissions.files.create;
		},
		canDrop() {
			return this.canAdd;
		},
		icon() {
			return "image";
		},
		type() {
			return "files";
		},
		uploadOptions() {
			return {
				...this.state.upload,
				url: this.$panel.urls.api + "/" + this.state.upload.api
			};
		},
		/**
		 * The list is only validated while it shows everything,
		 * as a search narrows it down to a part of the collection
		 */
		validator() {
			const count = this.state.pagination.total;

			if (this.searchterm) {
				return { count };
			}

			return { count, max: this.max, min: this.min };
		}
	},
	methods: {
		onAction(action, file) {
			if (action === "replace") {
				this.$panel.upload.replace(file, this.uploadOptions);
			}
		},
		onAdd() {
			if (this.canAdd === true) {
				this.$panel.upload.pick(this.uploadOptions);
			}
		},
		onDrop(files) {
			if (this.canDrop === true) {
				this.$panel.upload.open(files, this.uploadOptions);
			}
		},
		async onSort(items) {
			if (this.isSortable === false) {
				return false;
			}

			await this.request(() =>
				this.$api.patch(this.endpoints.field + "/sort", {
					files: items.map((item) => item.id),
					index: this.state.pagination.offset
				})
			);
		},
		refreshEvents() {
			return ["file.sort", "model.update"];
		}
	}
};
</script>
