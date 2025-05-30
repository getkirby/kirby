import { reactive } from "vue";
import { uuid } from "@/helpers/string";
import State from "./state.js";
import listeners from "./listeners.js";
import queue from "@/helpers/queue.js";
import { uploadAsChunks } from "@/helpers/upload.js";
import { extension, name, niceSize } from "@/helpers/file.js";

export const defaults = () => {
	return {
		abort: null,
		accept: "*",
		attributes: {},
		files: [],
		max: null,
		multiple: true,
		preview: {},
		replacing: null,
		url: null
	};
};

/**
 * Handle file uploads in the Panel
 *
 * Basic overview of the chain of methods:
 *
 * pick   ‾\                      /‾ done
 *          -- (open) -- submit --
 * select  _/                     \_ cancel
 *
 * @since 4.0.0
 */
export default (panel) => {
	const parent = State("upload", defaults());

	return reactive({
		...parent,
		...listeners(),
		input: null,
		announce() {
			panel.notification.success({ context: "view" });
			panel.events.emit("model.update");
		},
		/**
		 * Called when dialog's cancel button was clicked
		 */
		async cancel() {
			await this.emit("cancel");

			// abort any ongoing requests
			this.abort?.abort();

			// emit complete event if any files have been completed,
			// e.g. when first submit/upload yielded any errors and
			// now cancel was clicked, but already some files have
			// been completely uploaded
			if (this.completed.length > 0) {
				await this.emit("complete", this.completed);
				this.announce();
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
		async done() {
			panel.dialog.close();

			if (this.completed.length > 0) {
				await this.emit("complete", this.completed);
				await this.emit("done", this.completed);
				this.announce();
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
		hasUniqueName(file) {
			return (
				this.files.filter(
					(f) => f.name === file.name && f.extension === file.extension
				).length < 2
			);
		},
		file(file) {
			const url = URL.createObjectURL(file);

			return {
				...this.preview,
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
				props: {
					preview: this.preview
				},
				on: {
					open: (dialog) => this.emit("open", dialog),
					cancel: () => this.cancel(),
					submit: async () => {
						panel.dialog.isLoading = true;
						await this.submit();
						panel.dialog.isLoading = false;
					}
				}
			};

			// when replacing a file, use dedicated dialog component
			if (this.replacing) {
				dialog.component = "k-upload-replace-dialog";
				dialog.props.original = this.replacing;
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
				if (options?.immediate === true) {
					// if upload should start immediately
					this.set(options);
					this.select(event.target.files);
					this.submit();
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
		async submit() {
			if (!this.url) {
				throw new Error("The upload URL is missing");
			}

			// prepare the abort controller
			this.abort = new AbortController();

			// gather upload tasks for all files
			const files = [];

			for (const file of this.files) {
				// skip file if alreay completed
				if (file.completed === true) {
					continue;
				}

				// ensure that all files have a unique name
				if (this.hasUniqueName(file) === false) {
					file.error = panel.t("error.file.name.unique");
					continue;
				}

				// reset progress and error before
				// the upload starts
				file.error = null;
				file.progress = 0;

				// clone the attributes to ensure that
				// each file has its own copy, e.g. of sort
				// (otherwise all files would use the state
				// of attributes from the last file in the loop)
				const attributes = { ...this.attributes };

				// add file to upload queue
				files.push(async () => await this.upload(file, attributes));

				const sort = this.attributes?.sort;

				// if there is sort data, increment in the loop for next file
				if (sort !== undefined && sort !== null) {
					this.attributes.sort++;
				}
			}

			await queue(files);

			// if no uncompleted files are left, be done
			if (this.files.length === this.completed.length) {
				return this.done();
			}
		},
		async upload(file, attributes) {
			try {
				const response = await uploadAsChunks(
					file.src,
					{
						abort: this.abort.signal,
						attributes: attributes,
						filename: file.name + "." + file.extension,
						headers: { "x-csrf": panel.system.csrf },
						url: this.url,
						progress: (xhr, src, progress) => {
							file.progress = progress;
						}
					},
					panel.config.upload
				);

				file.completed = true;
				file.model = response.data;

				panel.events.emit("file.upload", file);
			} catch (error) {
				panel.error(error, false);

				// store the error message to show it in
				// the dialog for example
				file.error = error.message;

				// reset the progress bar on error
				file.progress = 0;
			}
		}
	});
};
