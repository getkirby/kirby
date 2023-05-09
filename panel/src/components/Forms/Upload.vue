<template></template>

<script>
/**
 * The Upload component is a combination of a native file input
 * and a dialog. The native file input is invisible and only
 * serves to open the file selector from the OS. Once files are
 * selected the dialog will open and show the progress and
 * potential upload errors.
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
	methods: {
		/**
		 * Opens the uploader with the object of given parameters.
		 * For all available parameters, check out the component props.
		 * If no additional parameters are passed, the properties from
		 * the upload element are used.
		 * @public
		 * @param {object} params
		 */
		open(params) {
			this.$panel.upload.open({
				...this.$props,
				...(params || {})
			});
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
		 */
		drop(files, params) {
			this.$panel.upload.drop(files, {
				...this.$props,
				...(params || {})
			});
		},
		upload(files) {
			this.$panel.upload.select(files, {
				...this.$props,
				...(params || {})
			});

			this.$panel.upload.start();
		}
	}
};
</script>
