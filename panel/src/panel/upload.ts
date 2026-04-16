import { reactive } from "vue";
import { uuid } from "@/helpers/string";
import State from "./state";
import listeners, { type Listener } from "./listeners";
import queue from "@/helpers/queue";
import { uploadAsChunks } from "@/helpers/upload";
import { extension, name, niceSize } from "@/helpers/file";

type UploadFileData = {
	completed: boolean;
	error?: string;
	extension: string;
	filename: string;
	id: string;
	model?: unknown;
	name: string;
	niceSize: string;
	progress: number;
	size: number;
	src: File;
	type: string;
	url: string;
	[key: string]: unknown;
};

type UploadState = {
	accept: string;
	attributes: Record<string, string | number>;
	files: UploadFileData[];
	max: number | null;
	multiple: boolean;
	on: Record<string, Listener>;
	preview: Record<string, string>;
	replacing: UploadFileData | null;
	url: string | null;
};

export function defaults(): UploadState {
	return {
		accept: "*",
		attributes: {},
		files: [],
		max: null,
		multiple: true,
		on: {},
		preview: {},
		replacing: null,
		url: null
	};
}

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
export default function Upload(panel: TODO) {
	const parent = State("upload", defaults());

	return reactive({
		...parent,
		...listeners(),
		abort: undefined as AbortController | undefined,

		announce(): void {
			panel.notification.success({ context: "view" });
			panel.events.emit("model.update", {
				path: this.replacing?.link
			});
		},
		/**
		 * Called when dialog's cancel button was clicked
		 */
		async cancel(): Promise<void> {
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
		get completed(): unknown[] {
			return this.files
				.filter((file) => file.completed)
				.map((file) => file.model);
		},
		/**
		 * Gets called when the dialog's submit button was clicked
		 * and all remaining files have been uploaded
		 */
		async done(): Promise<void> {
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
		 */
		findDuplicate(file: UploadFileData): number {
			return this.files.findLastIndex(
				(x) =>
					x.src.name === file.src.name &&
					x.src.type === file.src.type &&
					x.src.size === file.src.size &&
					x.src.lastModified === file.src.lastModified
			);
		},
		file(file: File): UploadFileData {
			const url = URL.createObjectURL(file);

			return {
				...this.preview,
				completed: false,
				extension: extension(file.name),
				filename: file.name,
				id: uuid(),
				name: name(file.name),
				niceSize: niceSize(file.size),
				progress: 0,
				size: file.size,
				src: file,
				type: file.type,
				url: url
			};
		},
		hasUniqueName(file: UploadFileData): boolean {
			return (
				this.files.filter(
					(f) => f.name === file.name && f.extension === file.extension
				).length < 2
			);
		},
		/**
		 * Opens the file dialog
		 */
		open(
			files: FileList | Partial<UploadState>,
			options?: Partial<UploadState>
		): void {
			if (files instanceof FileList) {
				this.set(options);
				this.select(files);
			} else {
				// allow options being defined as first argument
				this.set(files);
			}

			panel.dialog.open({
				component: this.replacing
					? "k-upload-replace-dialog"
					: "k-upload-dialog",
				props: {
					preview: this.preview,
					original: this.replacing
				},
				on: {
					open: (dialog: TODO) => this.emit("open", dialog),
					cancel: () => this.cancel(),
					submit: async () => {
						panel.dialog.isLoading = true;
						await this.submit();
						panel.dialog.isLoading = false;
					}
				}
			});
		},
		/**
		 * Open the system file picker
		 */
		pick(options: Partial<UploadState & { immediate: boolean }>): void {
			this.set(options);

			// create a new temporary file input
			const input = document.createElement("input") as HTMLInputElement;
			input.type = "file";
			input.classList.add("sr-only");
			input.value = "";
			input.accept = this.accept;
			input.multiple = this.multiple;

			// open the file picker
			input.click();

			// show the dialog on change
			input.addEventListener("change", (event) => {
				const target = event.target as HTMLInputElement;
				const files = target.files;

				if (!files) {
					return;
				}

				if (options?.immediate === true) {
					// if upload should start immediately
					this.set(options);
					this.select(files);
					this.submit();
				} else {
					this.open(files, options);
				}

				input.remove();
			});
		},
		remove(id: string): void {
			this.files = this.files.filter((file) => file.id !== id);
		},
		replace(file: UploadFileData, options: Partial<UploadState>): void {
			this.pick({
				...options,
				url: panel.urls.api + "/" + file.link,
				accept: "." + file.extension + "," + file.mime,
				multiple: false,
				replacing: file
			});
		},
		reset(): void {
			parent.reset.call(this);
			this.files.splice(0);
		},
		select(
			filelist: FileList | InputEvent | null,
			options?: Partial<UploadState>
		): void {
			this.set(options);

			if (filelist instanceof Event) {
				const target = filelist.target as HTMLInputElement;
				filelist = target.files;
			}

			if (filelist instanceof FileList === false) {
				throw new Error("Please provide a FileList");
			}

			// convert the file list to an array
			const files: File[] = [...filelist];

			// add all files to the list as enriched objects
			const data: UploadFileData[] = files.map((file) => this.file(file));

			// merge the new files with already selected files
			this.files = [...this.files, ...data];

			// remove duplicates by comparing crucial src attributes,
			// preserving the newer file
			this.files = this.files.filter(
				(file, index) => this.findDuplicate(file) === index
			);

			// apply the max limit to the list of files
			if (this.max !== null) {
				// slice from the end to keep the latest files
				this.files = this.files.slice(-this.max);
			}

			this.emit("select", this.files);
		},
		set(state?: Partial<UploadState>): UploadState | undefined {
			if (!state) {
				return;
			}

			parent.set.call(this, state);

			// reset the event listeners
			this.removeEventListeners();

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
		async submit(): Promise<void> {
			if (!this.url) {
				throw new Error("The upload URL is missing");
			}

			// prepare the abort controller
			this.abort = new AbortController();

			// gather upload tasks for all files
			const files = [];

			for (const file of this.files) {
				// skip file if already completed
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
				file.error = undefined;
				file.progress = 0;

				// clone the attributes to ensure that
				// each file has its own copy, e.g. of sort
				// (otherwise all files would use the state
				// of attributes from the last file in the loop)
				const attributes = { ...this.attributes };

				// add file to upload queue
				files.push(() => this.upload(file, attributes));

				// if there is sort data, increment in the loop for next file
				const sort = this.attributes.sort;

				if (sort !== undefined && sort !== null) {
					(this.attributes.sort as number)++;
				}
			}

			await queue(files);

			// if no uncompleted files are left, be done
			if (this.files.length === this.completed.length) {
				return this.done();
			}
		},
		async upload(
			file: UploadFileData,
			attributes: Record<string, string | number>
		): Promise<void> {
			try {
				const response = (await uploadAsChunks(
					file.src,
					{
						abort: this.abort!.signal,
						attributes: attributes,
						filename: file.name + "." + file.extension,
						headers: { "x-csrf": panel.system.csrf },
						url: this.url ?? undefined,
						progress: (xhr, src, progress) => {
							file.progress = progress;
						}
					},
					panel.config.upload
				)) as Record<string, unknown>;

				file.completed = true;
				file.model = response.data;

				panel.events.emit("file.upload", file);
			} catch (error) {
				panel.error(error, false);

				// store the error message to show it in
				// the dialog for example
				if (error instanceof Error) {
					file.error = error.message;
				}

				// reset the progress bar on error
				file.progress = 0;

				panel.events.emit("file.upload.error", file);
			}
		}
	});
}
