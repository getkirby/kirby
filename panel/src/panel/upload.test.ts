import { describe, expect, it, onTestFinished, vi } from "vitest";
import Upload, { defaults, type UploadState } from "./upload";
import Panel from "./panel";

// URL.createObjectURL/.revokeObjectURL
// are not implemented in happy-dom
URL.createObjectURL = vi.fn(() => "blob:mock-url");
URL.revokeObjectURL = vi.fn();

type FakeRequest = {
	body: FormData | null;
	headers: Record<string, string>;
	url: string;
};

type Listener = (event: object) => void;

function flush(): Promise<void> {
	return new Promise((resolve) => setTimeout(resolve));
}

/**
 * Creates a Panel with a real chunk size, as the
 * backend sends it (the default 0 never ends chunking)
 */
function createPanel(): Panel {
	const panel = Panel.create(app);
	panel.config.upload = 5242880;
	return panel;
}

/**
 * Replaces XMLHttpRequest with a fake that records each request
 * and answers it with the given response after some progress
 */
function fakeRequests(
	respond: () => Record<string, unknown> = () => ({ status: "ok", data: {} })
): FakeRequest[] {
	const requests: FakeRequest[] = [];
	const original = globalThis.XMLHttpRequest;

	class FakeXhr {
		listeners: Record<string, Listener> = {};
		progress: Listener = () => {};
		request: FakeRequest = { body: null, headers: {}, url: "" };
		response = "";
		upload = {
			addEventListener: (type: string, listener: Listener) => {
				if (type === "progress") {
					this.progress = listener;
				}
			}
		};

		aborted = false;

		abort(): void {
			this.aborted = true;
			this.listeners.abort?.({ target: this });
		}

		addEventListener(type: string, listener: Listener): void {
			this.listeners[type] = listener;
		}

		open(method: string, url: string): void {
			this.request.url = url;
		}

		send(body: FormData): void {
			this.request.body = body;
			requests.push(this.request);

			setTimeout(() => {
				if (this.aborted === true) {
					return;
				}

				this.progress({ lengthComputable: true, loaded: 1, total: 2 });
				this.response = JSON.stringify(respond());
				this.listeners.load({ target: this });
			});
		}

		setRequestHeader(key: string, value: string): void {
			this.request.headers[key] = value;
		}
	}

	globalThis.XMLHttpRequest = FakeXhr as unknown as typeof XMLHttpRequest;

	onTestFinished(() => {
		globalThis.XMLHttpRequest = original;
	});

	return requests;
}

function makeFile(name = "test.jpg", type = "image/jpeg", size = 100): File {
	return new File([new ArrayBuffer(size)], name, { type });
}

function makeFileList(...files: File[]): FileList {
	const list = Object.assign(
		Object.create(FileList.prototype),
		{ length: files.length, item: (i: number) => files[i] ?? null },
		{ [Symbol.iterator]: () => files[Symbol.iterator]() },
		...files.map((file, i) => ({ [i]: file }))
	);
	return list as FileList;
}

/**
 * A file model, as views and fields pass it to `replace()`
 */
function model(): NonNullable<UploadState["replacing"]> {
	return {
		extension: "jpg",
		filename: "a.jpg",
		link: "pages/test/files/a.jpg",
		mime: "image/jpeg"
	} as unknown as NonNullable<UploadState["replacing"]>;
}

/**
 * Captures the file input that `pick()` clicks
 * instead of opening the system file picker
 */
function picker() {
	const inputs: HTMLInputElement[] = [];
	const click = vi
		.spyOn(HTMLInputElement.prototype, "click")
		.mockImplementation(function (this: HTMLInputElement) {
			inputs.push(this);
		});

	onTestFinished(() => click.mockRestore());

	return {
		get input(): HTMLInputElement | undefined {
			return inputs[0];
		},
		choose(...files: File[]): void {
			Object.defineProperty(inputs[0], "files", {
				value: makeFileList(...files)
			});
			inputs[0].dispatchEvent(new Event("change"));
		}
	};
}

describe("panel.upload", () => {
	describe("defaults()", () => {
		it("should return the default state", () => {
			expect(defaults()).toStrictEqual({
				accept: "*",
				attributes: {},
				files: [],
				max: null,
				multiple: true,
				on: {},
				preview: {},
				replacing: null,
				url: null
			});
		});
	});

	describe("cancel()", () => {
		it("should emit a cancel event", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onCancel = vi.fn();
			upload.addEventListener("cancel", onCancel);
			await upload.cancel();
			expect(onCancel).toHaveBeenCalledOnce();
		});

		it("should not emit complete when no files have been uploaded", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onComplete = vi.fn();
			upload.addEventListener("complete", onComplete);
			await upload.cancel();
			expect(onComplete).not.toHaveBeenCalled();
		});

		it("should emit complete with uploaded file models when some files are done", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = upload.file(makeFile("a.jpg"));
			file.completed = true;
			file.model = { id: "a" };
			upload.files = [file];
			const onComplete = vi.fn();
			upload.addEventListener("complete", onComplete);
			await upload.cancel();
			expect(onComplete).toHaveBeenCalledWith([{ id: "a" }]);
		});

		it("should abort any ongoing request", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const controller = new AbortController();
			const spy = vi.spyOn(controller, "abort");
			upload.abort = controller;
			await upload.cancel();
			expect(spy).toHaveBeenCalledOnce();
		});

		it("should reset state after cancelling", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.files = [upload.file(makeFile("a.jpg"))];
			await upload.cancel();
			expect(upload.files).toHaveLength(0);
		});
	});

	describe("completed", () => {
		it("should return an empty array when no files exist", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			expect(upload.completed).toStrictEqual([]);
		});

		it("should return an empty array when no files are completed", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.files = [upload.file(makeFile("a.jpg"))];
			expect(upload.completed).toStrictEqual([]);
		});

		it("should only include completed files", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const done = upload.file(makeFile("a.jpg"));
			const pending = upload.file(makeFile("b.jpg"));
			done.completed = true;
			done.model = { id: "a" };
			upload.files = [done, pending];
			expect(upload.completed).toStrictEqual([{ id: "a" }]);
		});
	});

	describe("done()", () => {
		it("should only close the dialog when no file has been uploaded", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onComplete = vi.fn();
			const onDone = vi.fn();
			upload.addEventListener("complete", onComplete);
			upload.addEventListener("done", onDone);

			await panel.dialog.open({ component: "k-upload-dialog" });
			await upload.done();

			expect(panel.dialog.isOpen).toStrictEqual(false);
			expect(onComplete).not.toHaveBeenCalled();
			expect(onDone).not.toHaveBeenCalled();
		});
	});

	describe("findDuplicate()", () => {
		it("should return -1 when the files list is empty", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = upload.file(makeFile("a.jpg"));
			expect(upload.findDuplicate(file)).toStrictEqual(-1);
		});

		it("should return -1 when no duplicate exists", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.files = [upload.file(makeFile("a.jpg"))];
			expect(
				upload.findDuplicate(upload.file(makeFile("b.jpg")))
			).toStrictEqual(-1);
		});

		it("should return the index of a matching file", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const src = makeFile("a.jpg");
			upload.files = [upload.file(src)];
			expect(upload.findDuplicate(upload.file(src))).toStrictEqual(0);
		});

		it("should return the last index when multiple matches exist", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const src = makeFile("a.jpg");
			upload.files = [upload.file(src), upload.file(src)];
			expect(upload.findDuplicate(upload.file(src))).toStrictEqual(1);
		});
	});

	describe("hasUniqueName()", () => {
		it("should return true when only one file has that name", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = upload.file(makeFile("test.jpg"));
			upload.files = [file];
			expect(upload.hasUniqueName(file)).toStrictEqual(true);
		});

		it("should return false when two files share the same name and extension", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const a = upload.file(makeFile("test.jpg"));
			const b = upload.file(makeFile("test.jpg"));
			upload.files = [a, b];
			expect(upload.hasUniqueName(a)).toStrictEqual(false);
		});

		it("should return true when name matches but extension differs", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const jpg = upload.file(makeFile("test.jpg", "image/jpeg"));
			const png = upload.file(makeFile("test.png", "image/png"));
			upload.files = [jpg, png];
			expect(upload.hasUniqueName(jpg)).toStrictEqual(true);
		});
	});

	describe("open()", () => {
		it("should select the files and open the upload dialog", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			upload.open(makeFileList(makeFile("a.jpg")), {
				preview: { ratio: "1/1" },
				url: "/api/pages/test/files"
			});
			await flush();

			expect(upload.files).toHaveLength(1);
			expect(upload.url).toStrictEqual("/api/pages/test/files");
			expect(panel.dialog.isOpen).toStrictEqual(true);
			expect(panel.dialog.component).toStrictEqual("k-upload-dialog");
			expect(panel.dialog.props).toStrictEqual({
				original: null,
				preview: { ratio: "1/1" }
			});
		});

		it("should accept the options as first argument", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			upload.open({ multiple: false, url: "/api/pages/test/files" });

			expect(upload.files).toHaveLength(0);
			expect(upload.max).toStrictEqual(1);
			expect(upload.url).toStrictEqual("/api/pages/test/files");
			expect(panel.dialog.component).toStrictEqual("k-upload-dialog");
		});

		it("should open the replace dialog when replacing a file", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = model();

			upload.open({ replacing: file });

			expect(panel.dialog.component).toStrictEqual("k-upload-replace-dialog");
			expect(panel.dialog.props.original).toStrictEqual(file);
		});

		it("should emit an open event with the dialog", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onOpen = vi.fn();

			upload.open({ on: { open: onOpen } });

			expect(onOpen).toHaveBeenCalledWith(
				expect.objectContaining({ component: "k-upload-dialog" })
			);
		});

		it("should cancel the upload when the dialog is cancelled", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onCancel = vi.fn();

			upload.open(makeFileList(makeFile("a.jpg")), {
				on: { cancel: onCancel }
			});
			await flush();
			await panel.dialog.cancel();
			await flush();

			expect(onCancel).toHaveBeenCalledOnce();
			expect(upload.files).toHaveLength(0);
			expect(panel.dialog.isOpen).toStrictEqual(false);
		});

		it("should upload the files when the dialog is submitted", async () => {
			const panel = createPanel();
			const upload = Upload(panel);
			const loading: boolean[] = [];
			const requests = fakeRequests(() => {
				loading.push(panel.dialog.isLoading);
				return { status: "ok", data: {} };
			});

			upload.open(makeFileList(makeFile("a.jpg")), {
				url: "/api/pages/test/files"
			});
			await flush();
			await panel.dialog.submit();

			expect(requests).toHaveLength(1);
			expect(loading).toStrictEqual([true]);
			expect(panel.dialog.isLoading).toStrictEqual(false);
			expect(panel.dialog.isOpen).toStrictEqual(false);
		});

		it("should close the dialog after uploading files picked inside it", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			upload.open({ url: "/api/pages/test/files" });
			await flush();

			// files picked from inside the dialog open it again
			upload.open(makeFileList(makeFile("a.jpg")));
			await flush();

			upload.upload = vi.fn(async (file) => {
				file.completed = true;
			});

			await panel.dialog.submit();

			expect(upload.upload).toHaveBeenCalledOnce();
			expect(panel.dialog.isOpen).toStrictEqual(false);
		});

		it("should close the dialog when cancelling after picking files inside it", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			upload.open({ url: "/api/pages/test/files" });
			await flush();
			upload.open(makeFileList(makeFile("a.jpg")));
			await flush();

			await panel.dialog.cancel();
			await flush();

			expect(upload.files).toHaveLength(0);
			expect(panel.dialog.isOpen).toStrictEqual(false);
		});

		it("should go back to the previous dialog after uploading", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			await panel.dialog.open({ component: "k-text-dialog" });
			upload.open(makeFileList(makeFile("a.jpg")), {
				url: "/api/pages/test/files"
			});
			await flush();

			upload.upload = vi.fn(async (file) => {
				file.completed = true;
			});

			await panel.dialog.submit();

			expect(panel.dialog.isOpen).toStrictEqual(true);
			expect(panel.dialog.component).toStrictEqual("k-text-dialog");
		});
	});

	describe("pick()", () => {
		it("should open the system file picker", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const pick = picker();

			upload.pick({ accept: "image/*" });

			expect(pick.input?.type).toStrictEqual("file");
			expect(pick.input?.accept).toStrictEqual("image/*");
			expect(pick.input?.multiple).toStrictEqual(true);
		});

		it("should open the upload dialog with the chosen files", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const pick = picker();

			upload.pick({ url: "/api/pages/test/files" });
			pick.choose(makeFile("a.jpg"));

			expect(upload.files.map((file) => file.filename)).toStrictEqual([
				"a.jpg"
			]);
			expect(panel.dialog.component).toStrictEqual("k-upload-dialog");
		});

		it("should upload the chosen files immediately", async () => {
			const panel = createPanel();
			const upload = Upload(panel);
			const requests = fakeRequests();
			const pick = picker();
			const onDone = vi.fn();

			upload.pick({
				immediate: true,
				on: { done: onDone },
				url: "/api/pages/test/files"
			});
			pick.choose(makeFile("a.jpg"));

			await vi.waitFor(() => expect(onDone).toHaveBeenCalledOnce());
			expect(requests).toHaveLength(1);
			expect(panel.dialog.component).toStrictEqual(null);
		});
	});

	describe("remove()", () => {
		it("should revoke the object URL of the removed file", () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			vi.mocked(URL.revokeObjectURL).mockClear();

			upload.files = [upload.file(makeFile("a.jpg"))];
			const { id, url } = upload.files[0];
			upload.remove(id);

			expect(URL.revokeObjectURL).toHaveBeenCalledWith(url);
		});
	});

	describe("replace()", () => {
		it("should pick a single file to replace the given one", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const pick = picker();
			const file = model();

			panel.urls.api = "/api";
			upload.replace(file, { preview: { ratio: "1/1" } });

			expect(pick.input?.accept).toStrictEqual(".jpg,image/jpeg");
			expect(pick.input?.multiple).toStrictEqual(false);

			pick.choose(makeFile("b.jpg"));

			expect(upload.url).toStrictEqual("/api/pages/test/files/a.jpg");
			expect(panel.dialog.component).toStrictEqual("k-upload-replace-dialog");
			expect(panel.dialog.props).toStrictEqual({
				original: file,
				preview: { ratio: "1/1" }
			});
		});
	});

	describe("reset()", () => {
		it("should revoke the object URLs of all files", () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			vi.mocked(URL.revokeObjectURL).mockClear();

			upload.files = [
				upload.file(makeFile("a.jpg")),
				upload.file(makeFile("b.jpg"))
			];
			upload.reset();

			expect(URL.revokeObjectURL).toHaveBeenCalledTimes(2);
			expect(upload.files).toHaveLength(0);
		});
	});

	describe("select()", () => {
		it("should add selected files to the list", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			expect(upload.files).toHaveLength(0);
			upload.select(makeFileList(makeFile("a.jpg")));
			expect(upload.files).toHaveLength(1);
		});

		it("should merge with already selected files", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.select(makeFileList(makeFile("a.jpg")));
			upload.select(makeFileList(makeFile("b.jpg")));
			expect(upload.files).toHaveLength(2);
		});

		it("should deduplicate files within a single selection", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = makeFile("a.jpg");
			upload.select(makeFileList(file, file));
			expect(upload.files).toHaveLength(1);
		});

		it("should deduplicate across multiple selections, keeping the newer file", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = makeFile("a.jpg");
			upload.select(makeFileList(file));
			const [first] = upload.files;
			upload.select(makeFileList(file));
			expect(upload.files).toHaveLength(1);
			expect(upload.files[0]).not.toBe(first);
		});

		it("should revoke the dropped duplicate's object URL when re-selecting", () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = makeFile("a.jpg");

			upload.select(makeFileList(file));
			vi.mocked(URL.revokeObjectURL).mockClear();
			upload.select(makeFileList(file));

			// the older duplicate is dropped and its object URL revoked
			expect(URL.revokeObjectURL).toHaveBeenCalledTimes(1);
			expect(upload.files).toHaveLength(1);
		});

		it("should apply the max limit, keeping the latest files", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.set({ max: 2 });
			vi.mocked(URL.revokeObjectURL).mockClear();
			upload.select(
				makeFileList(makeFile("a.jpg"), makeFile("b.jpg"), makeFile("c.jpg"))
			);
			expect(upload.files).toHaveLength(2);
			expect(upload.files[0].filename).toStrictEqual("b.jpg");
			expect(upload.files[1].filename).toStrictEqual("c.jpg");

			// the file that exceeded the max limit had its object URL revoked
			expect(URL.revokeObjectURL).toHaveBeenCalledTimes(1);
		});

		it("should accept an InputEvent and read files from its target", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const file = makeFile("a.jpg");
			const input = document.createElement("input");
			Object.defineProperty(input, "files", { value: makeFileList(file) });
			const event = new Event("change");
			Object.defineProperty(event, "target", { value: input });
			upload.select(event as unknown as InputEvent);
			expect(upload.files).toHaveLength(1);
		});

		it("should throw when no FileList is provided", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			expect(() => upload.select(null)).toThrow("Please provide a FileList");
		});

		it("should emit a select event with the current file list", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onSelect = vi.fn();
			upload.addEventListener("select", onSelect);
			upload.select(makeFileList(makeFile("a.jpg")));
			expect(onSelect).toHaveBeenCalledOnce();
		});
	});

	describe("set()", () => {
		it("should return undefined when called without state", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			expect(upload.set()).toStrictEqual(undefined);
		});

		it("should force multiple to false when max is 1", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.set({ max: 1 });
			expect(upload.multiple).toStrictEqual(false);
		});

		it("should force max to 1 when multiple is false", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.set({ multiple: false });
			expect(upload.max).toStrictEqual(1);
		});

		it("should apply the multiple rule even when max is greater than 1", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			upload.set({ max: 3, multiple: false });
			expect(upload.max).toStrictEqual(1);
			expect(upload.multiple).toStrictEqual(false);
		});
	});

	describe("submit()", () => {
		it("should throw without an upload URL", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			await expect(upload.submit()).rejects.toThrow(
				"The upload URL is missing"
			);
		});

		it("should skip files that have already been uploaded", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			upload.url = "/api/pages/test/files";
			upload.select(makeFileList(makeFile("a.jpg"), makeFile("b.jpg")));
			upload.files[0].completed = true;
			upload.upload = vi.fn(async (file) => {
				file.completed = true;
			});

			await upload.submit();

			expect(upload.upload).toHaveBeenCalledOnce();
			expect(vi.mocked(upload.upload).mock.calls[0][0].filename).toStrictEqual(
				"b.jpg"
			);
		});

		it("should not upload files with the same name", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);
			const onDone = vi.fn();

			upload.url = "/api/pages/test/files";
			upload.addEventListener("done", onDone);
			upload.select(
				makeFileList(
					makeFile("a.jpg", "image/jpeg", 100),
					makeFile("a.jpg", "image/jpeg", 200)
				)
			);
			upload.upload = vi.fn();

			await upload.submit();

			expect(upload.upload).not.toHaveBeenCalled();
			expect(upload.files.map((file) => file.error)).toStrictEqual([
				"error.file.name.unique",
				"error.file.name.unique"
			]);
			expect(onDone).not.toHaveBeenCalled();
		});

		it("should increase the sort number for each file", async () => {
			const panel = Panel.create(app);
			const upload = Upload(panel);

			upload.set({ attributes: { sort: 3 }, url: "/api/pages/test/files" });
			upload.select(makeFileList(makeFile("a.jpg"), makeFile("b.jpg")));
			upload.upload = vi.fn(async (file) => {
				file.completed = true;
			});

			await upload.submit();

			expect(
				vi.mocked(upload.upload).mock.calls.map(([, attributes]) => attributes)
			).toStrictEqual([{ sort: 3 }, { sort: 4 }]);
		});

		it("should limit the number of parallel uploads", async () => {
			const panel = Panel.create(app);
			panel.set({ config: { uploads: 2 } });

			const upload = Upload(panel);
			const files = Array.from({ length: 8 }, (_, index) =>
				makeFile(`image-${index}.jpg`)
			);

			upload.url = "/api/pages/test/files";
			upload.select(makeFileList(...files));

			let active = 0;
			let max = 0;

			upload.upload = vi.fn(async (file) => {
				active++;
				max = Math.max(max, active);
				await new Promise((resolve) => setTimeout(resolve, 5));
				active--;
				file.completed = true;
			});

			await upload.submit();

			expect(upload.upload).toHaveBeenCalledTimes(8);
			expect(max).toStrictEqual(2);
		});
	});

	describe("upload()", () => {
		it("should upload the file with its name and attributes", async () => {
			const panel = createPanel();
			const upload = Upload(panel);
			const requests = fakeRequests(() => ({
				status: "ok",
				data: { id: "a" }
			}));
			const onUpload = vi.fn();
			const file = upload.file(makeFile("a.jpg"));

			panel.set({ system: { csrf: "token" } });
			panel.events.on("file.upload", onUpload);
			upload.abort = new AbortController();
			upload.url = "/api/pages/test/files";
			file.name = "renamed";

			await upload.upload(file, { sort: 2 });

			expect(requests).toHaveLength(1);
			expect(requests[0].url).toStrictEqual("/api/pages/test/files");
			expect(requests[0].headers).toStrictEqual({ "x-csrf": "token" });
			expect((requests[0].body?.get("file") as File).name).toStrictEqual(
				"renamed.jpg"
			);
			expect(requests[0].body?.get("sort")).toStrictEqual("2");
			expect(file.completed).toStrictEqual(true);
			expect(file.model).toStrictEqual({ id: "a" });
			expect(file.progress).toStrictEqual(100);
			expect(onUpload).toHaveBeenCalledWith(file);
		});

		it("should reset the file when the upload fails", async () => {
			const panel = createPanel();
			const upload = Upload(panel);
			const onError = vi.fn();
			const file = upload.file(makeFile("a.jpg"));

			fakeRequests(() => ({ status: "error", message: "Not allowed" }));
			panel.events.on("file.upload.error", onError);
			upload.abort = new AbortController();
			upload.url = "/api/pages/test/files";

			await upload.upload(file, {});

			expect(file.completed).toStrictEqual(false);
			expect(file.error).toStrictEqual("Not allowed");
			expect(file.progress).toStrictEqual(0);
			expect(onError).toHaveBeenCalledWith(file);
		});

		it("should not treat an aborted upload as an error", async () => {
			const panel = createPanel();
			const upload = Upload(panel);
			const onError = vi.fn();
			const file = upload.file(makeFile("a.jpg"));

			fakeRequests();
			panel.events.on("file.upload.error", onError);
			upload.abort = new AbortController();
			upload.url = "/api/pages/test/files";
			file.progress = 50;

			const uploading = upload.upload(file, {});
			upload.abort.abort();
			await uploading;

			expect(file.completed).toStrictEqual(false);
			expect(file.error).toBeUndefined();
			expect(file.progress).toStrictEqual(50);
			expect(onError).not.toHaveBeenCalled();
		});
	});
});
