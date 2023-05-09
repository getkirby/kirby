import { uuid } from "@/helpers/string";
import Module from "./module.js";
import listeners from "./listeners.js";
import upload from "@/helpers/upload.js";
import { extension, name, niceSize } from "@/helpers/file.js";

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
		...listeners(),
		input: null,
		cancel() {
			this.emit("cancel");
			this.close();
		},
		close() {
			this.emit("close");
			this.reset();
		},
		/**
		 * Opens the file dialog
		 *
		 * @param {FileList} files
		 * @param {Object} options
		 */
		open(files, options) {
			if (files instanceof FileList) {
				this.set(options);
				this.select(files);
			} else {
				// options can be defined as first argument
				this.set(files);
			}

			panel.dialog.open({
				component: "k-upload-dialog",
				on: {
					close: () => {
						this.close();
					},
					submit: () => {
						this.start();
					}
				}
			});
		},
		/**
		 * Open the system file picker
		 *
		 * @param {Object} options
		 */
		pick(options) {
			this.set(options);

			// create a new temporary file input
			this.input = document.createElement("input");
			this.input.type = "file";
			this.input.classList.add("sr-only");
			this.input.value = null;
			this.input.accept = this.accept;
			this.input.multiple = this.multiple;

			// open the file picker
			this.input.click();

			// show the dialog on change
			this.input.addEventListener("change", (event) => {
				this.open(event.target.files, options);
				this.input.remove();
			});
		},
		remove(id) {
			this.files = this.files.filter((file) => file.id !== id);
		},
		reset() {
			parent.reset.call(this);
			this.files.splice(0);
		},
		select(files, options) {
			this.set(options);

			if (files instanceof Event) {
				files = files.target.files;
			}

			if (files instanceof FileList === false) {
				throw new Error("Please provide a FileList");
			}

			// convert the file list to an array
			files = [...files];

			// add all files to the list
			this.files = files.map((file) => {
				const url = URL.createObjectURL(file);

				return {
					completed: false,
					error: null,
					extension: extension(file.name),
					filename: file.name,
					id: uuid(),
					model: null,
					name: name(file.name),
					niceSize: niceSize(file.size),
					progress: 0,
					size: file.size,
					src: file,
					type: file.type,
					url: url
				};
			});

			// apply the max limit to the list of files
			if (this.max > 1) {
				this.files = this.files.slice(0, this.max);
			}

			this.emit("select", this.files);
		},
		set(state) {
			if (!state) {
				return;
			}

			parent.set.call(this, state);

			// reset the event listeners
			this.on = {};

			// register new listeners
			this.addEventListeners(state.on || {});

			if (this.max === 1) {
				this.multiple = false;
			}

			if (this.multiple === false) {
				this.max = 1;
			}

			return this.state();
		},
		start() {
			if (!this.url) {
				throw new Error("The upload URL is missing");
			}

			// nothing to upload
			if (this.files.length === 0) {
				return;
			}

			// upload each file individually and keep track of the progress
			this.files.forEach((file) => {
				// don't upload completed files again
				if (file.completed === true) {
					return;
				}

				// reset progress and error before
				// the upload starts
				file.error = null;
				file.progress = 0;

				upload(file.src, {
					attributes: this.attributes,
					headers: {
						"x-csrf": panel.system.csrf
					},
					filename: file.name + "." + file.extension,
					url: this.url,
					error: (xhr, src, response) => {
						// store the error message to show it in
						// the dialog for example
						file.error = response.message;

						// reset the progress bar on error
						file.progress = 0;
					},
					progress: (xhr, src, progress) => {
						file.progress = progress;
					},
					success: (xhr, src, response) => {
						file.completed = true;
						file.model = response.data;

						const remaining = this.files.filter((file) => {
							return file.completed === false;
						}).length;

						if (remaining === 0) {
							panel.view.reload();
							this.emit(
								"complete",
								this.files,
								this.files
									.filter((file) => file.completed)
									.map((file) => file.model)
							);
							this.close();
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
