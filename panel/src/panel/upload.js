import { uuid } from "@/helpers/string";
import State from "./state.js";
import listeners from "./listeners.js";
import upload from "@/helpers/upload.js";
import { extension, name, niceSize } from "@/helpers/file.js";

export const defaults = () => {
	return {
		accept: "*",
		attributes: {},
		files: [],
		max: null,
		multiple: true,
		replacing: null,
		url: null
	};
};

/**
 * Basic overview of the chain of methods:
 *
 * pick   ‾\                     /‾ done
 *          -- (open) -- start --
 * select  _/                     \_ cancel
 */
export default (panel) => {
	const parent = State("upload", defaults());

	return {
		...parent,
		...listeners(),
		input: null,
		/**
		 * Called when dialog's cancel button was clicked
		 */
		cancel() {
			this.emit("cancel");

			// emit complete event if any files have been completed,
			// e.g. when first submit/upload yielded any errors and
			// now cancel was clicked, but already some files have
			// been completely uploaded
			if (this.completed.length > 0) {
				this.emit("complete", this.completed);
				panel.view.reload();
			}

			this.reset();
		},
		/**
		 * All files that've been already uploaded
		 */
		get completed() {
			return this.files
				.filter((file) => file.completed)
				.map((file) => file.model);
		},
		/**
		 * Gets called when the dialog's submit button was clicked
		 * and all remaining files have been uploaded
		 */
		done() {
			panel.dialog.close();

			if (this.completed.length > 0) {
				this.emit("done", this.completed);

				if (panel.drawer.isOpen === false) {
					panel.notification.success({ context: "view" });
					panel.view.reload();
				}
			}

			this.reset();
		},
		/**
		 * Checks if file already exists in files list
		 * and returns index if so
		 *
		 * @param {Object} file
		 * @returns {Number|false}
		 */
		findDuplicate(file) {
			return this.files.findLastIndex(
				(x) =>
					x.src.name === file.src.name &&
					x.src.type === file.src.type &&
					x.src.size === file.src.size &&
					x.src.lastModified === file.src.lastModified
			);
		},
		file(file) {
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
				// allow options being defined as first argument
				this.set(files);
			}

			const dialog = {
				component: "k-upload-dialog",
				on: {
					cancel: () => this.cancel(),
					submit: () => this.start()
				}
			};

			// when replacing a file, use decdicated dialog component
			if (this.replacing) {
				dialog.component = "k-upload-replace-dialog";
				dialog.props = { original: this.replacing };
			}

			panel.dialog.open(dialog);
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
				if (options.immediate === true) {
					// if upload should start immediately
					this.set(options);
					this.select(event.target.files);
					this.start();
				} else {
					this.open(event.target.files, options);
				}

				this.input.remove();
			});
		},
		remove(id) {
			this.files = this.files.filter((file) => file.id !== id);
		},
		replace(file, options) {
			this.pick({
				...options,
				url: panel.urls.api + "/" + file.link,
				accept: "." + file.extension + "," + file.mime,
				multiple: false,
				replacing: file
			});
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

			// add all files to the list as enriched objects
			files = files.map((file) => this.file(file));

			// merge the new files with already selected files
			this.files = [...this.files, ...files];

			// remove duplicates by comparing crucial src attributes,
			// preserving the newer file
			this.files = this.files.filter(
				(file, index) => this.findDuplicate(file) === index
			);

			// apply the max limit to the list of files
			if (this.max !== null) {
				// slice from the end to keep the latest files
				this.files = this.files.slice(-1 * this.max);
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
			this.addEventListeners(state.on ?? {});

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

			// if no uncompleted files are left, be done
			if (this.files.length === this.completed.length) {
				return this.done();
			}

			// gather upload queue for all files
			const queue = [];

			for (const file of this.files) {
				// skip file if alreay completed
				if (file.completed === true) {
					continue;
				}

				// ensure that all files have a unique name
				if (
					this.files.filter(
						(f) => f.name === file.name && f.extension === file.extension
					).length > 1
				) {
					file.error = panel.t("error.file.name.unique");
					continue;
				}

				// reset progress and error before
				// the upload starts
				file.error = null;
				file.progress = 0;

				// add file to upload queue
				queue.push(file);

				// if there is sort data, increment in the loop for next file
				if (this.attributes?.sort !== undefined) {
					this.attributes.sort++;
				}
			}

			// async uploader function:
			// uploads the next file in the queue
			// and triggers itself again after completion
			const uploader = async () => {
				if (queue.length === 0) {
					return;
				}

				try {
					const file = queue.shift();
					await upload(file.src, {
						attributes: this.attributes,
						headers: {
							"x-csrf": panel.system.csrf
						},
						filename: file.name + "." + file.extension,
						url: this.url,
						error: (xhr, src, response) => {
							panel.error(response, false);

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

							if (this.files.length === this.completed.length) {
								this.done();
							}
						}
					});
				} finally {
					uploader();
				}
			};

			// initialize the uploader for the first up to 20 files,
			// uploader function will then trigger itself after completion
			// until the full queue has been processed
			for (let i = 0; i < Math.min(queue.length, 20); i++) {
				uploader();
			}
		}
	};
};
