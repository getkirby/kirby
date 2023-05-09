import { uuid } from "@/helpers/string";
import Module from "./module.js";
import upload from "@/helpers/upload.js";

export const defaults = () => {
	return {
		accept: "*",
		attributes: {},
		max: null,
		multiple: true,
		url: null,
		files: []
	};
};

export default (panel) => {
	const parent = Module("upload", defaults());

	return {
		...parent,
		input: null,
		isEmpty() {
			return this.files.length === 0;
		},
		close() {
			if (panel.dialog.component === "k-upload-dialog") {
				panel.dialog.close();
			}
		},
		dialog(options) {
			if (options) {
				this.set(options);
			}

			panel.dialog.open({
				component: "k-upload-dialog",
				on: {
					close: () => {
						this.reset();
					},
					submit: () => {
						this.start();
					}
				}
			});
		},
		drop(files, options) {
			if (options) {
				this.set(options);
			}

			this.select(files);
		},
		open(options) {
			if (options) {
				this.set(options);
			}

			if (this.max === 1) {
				this.multiple = false;
			}

			if (this.multiple === false) {
				this.max = 1;
			}

			this.input = document.querySelector("#uploader");
			this.input.value = null;
			this.input.accept = this.accept;
			this.input.multiple = this.multiple;

			this.input.click();
		},
		reset() {
			parent.reset.call(this);
			this.files.splice(0);
		},
		select(files, options) {
			if (options) {
				this.set(options);
			}

			if (files instanceof Event) {
				files = files.target.files;
			}

			if (files instanceof FileList === false) {
				throw new Error("Please provide a FileList");
			}

			files = [...files];

			// apply the max limit to the list of files
			if (this.max > 1) {
				files = files.slice(0, this.max);
			}

			const formatter = Intl.NumberFormat("en", {
				notation: "compact",
				style: "unit",
				unit: "byte",
				unitDisplay: "narrow"
			});

			// add all files to the list
			files.forEach((file) => {
				const url = URL.createObjectURL(file);
				const parts = file.name.split(".");

				this.files.push({
					name: parts.slice(0, -1).join("."),
					error: null,
					extension: parts.slice(-1).join(""),
					filename: file.name,
					niceSize: formatter.format(file.size),
					progress: 0,
					size: file.size,
					src: file,
					type: file.type,
					upload: true,
					url: url,
					uuid: uuid()
				});
			});

			this.dialog();
		},
		start(url) {
			if (url) {
				this.url = url;
			}

			// only keep the ones that have been marked for the upload
			this.files = this.files.filter((file) => file.upload);

			// nothing to upload
			if (this.files.length === 0) {
				this.close();
				panel.reload();
				return;
			}

			if (!this.url) {
				throw new Error("The upload URL is missing");
			}

			this.files
				.filter((file) => file.upload === true)
				.forEach((file) => {
					upload(file.src, {
						attributes: this.attributes,
						headers: {
							"x-csrf": panel.system.csrf
						},
						filename: file.name + "." + file.extension,
						url: this.url,
						error: (xhr, src, response) => {
							file.error = response.message;
							file.progress = 0;
						},
						progress: (xhr, src, progress) => {
							file.progress = progress;
						},
						success: () => {
							this.files = this.files.filter((f) => f !== file);

							const remaining = this.files.filter((file) => {
								// incomplete / with errors / not selected
								return file.progress !== 100 || file.error;
							}).length;

							if (remaining === 0) {
								this.close();
								panel.view.reload();
							}
						}
					});

					// if there is sort data, increment in the loop for next file
					if (this.attributes?.sort !== undefined) {
						this.attributes.sort++;
					}
				});
		}
	};
};
