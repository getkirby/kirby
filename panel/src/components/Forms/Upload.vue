<script>
/**
 * The Upload component is a combination of a native file input
 * and a dialog. The native file input is invisible and only
 * serves to open the file selector from the OS. Once files are
 * selected the dialog will open and show the progress and
 * potential upload errors.
 *
 * @deprecated 4.0.0 Use the $panel.upload module instead
 */
export default {
	props: {
		accept: {
			type: String,
			default: "*"
		},
		attributes: {
			type: Object
		},
		max: {
			type: Number
		},
		method: {
			type: String,
			default: "POST"
		},
		multiple: {
			type: Boolean,
			default: true
		},
		url: {
			type: String
		}
	},
	emits: ["success"],
	methods: {
		/**
		 * Opens the uploader with the object of given parameters.
		 * For all available parameters, check out the component props.
		 * If no additional parameters are passed, the properties from
		 * the upload element are used.
		 * @public
		 * @param {object} params
		 * @deprecated 4.0.0
		 */
		open(params) {
			window.panel.deprecated(
				"<k-upload> will be removed in a future version. Use `$panel.upload.open()` instead."
			);

			this.$panel.upload.pick(this.params(params));
		},
		params(params) {
			return {
				...this.$props,
				...(params ?? {}),
				on: {
					complete: (files, models) => {
						this.$emit("success", files, models);
					}
				}
			};
		},
		select(e) {
			this.$panel.upload.select(e.target.files);
		},
		/**
		 * Instead of opening the file picker first
		 * you can also start the uploader directly,
		 * by "dropping" a FileList from a drop event
		 * for example.
		 * @public
		 * @param {array} files
		 * @param {object} params
		 * @deprecated 4.0.0
		 */
		drop(files, params) {
			window.panel.deprecated(
				"<k-upload> will be removed in a future version. Use `$panel.upload.select()` instead."
			);

			this.$panel.upload.open(files, this.params(params));
		},
		/**
		 * @deprecated 4.0.0
		 */
		upload(files, params) {
			window.panel.deprecated(
				"<k-upload> will be removed in a future version. Use `$panel.upload.select()` instead."
			);

			this.$panel.upload.select(files, this.params(params));
			this.$panel.upload.start();
		}
	},
	render() {
		return "";
	}
};
</script>
