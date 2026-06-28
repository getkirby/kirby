import { describe, expect, it, vi } from "vitest";
import Upload, { defaults } from "./upload";
import Panel from "./panel";

// URL.createObjectURL/.revokeObjectURL
// are not implemented in happy-dom
URL.createObjectURL = vi.fn(() => "blob:mock-url");
URL.revokeObjectURL = vi.fn();

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
});
