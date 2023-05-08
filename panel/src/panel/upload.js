import { uuid } from "@/helpers/string";
import Module from "./module.js";
import { reactive, ref } from "vue";

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
		dialog() {
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
		drop(files) {
			this.select(files);
		},
		open(options = {}) {
			this.set(options);

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
		select(files) {
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
					alt: null,
					name: parts.slice(0, -1).join("."),
					extension: parts.slice(-1).join(""),
					niceSize: formatter.format(file.size),
					filename: file.name,
					size: file.size,
					src: file,
					type: file.type,
					url: url,
					uuid: uuid()
				});
			});
		},
		start(url) {
			if (url) {
				this.url = url;
			}

			// nothing to upload
			if (this.files.length === 0) {
				return true;
			}

			if (!this.url) {
				throw new Error("The upload URL is missing");
			}

			alert("starting the upload");
		}
	};
};
