import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import FilePickerField from "./FilePickerField.vue";

// registered globally in the Panel
const Collection = { props: ["empty", "items"], template: "<div />" };

const api = { get: vi.fn() };
const events = { off: vi.fn(), on: vi.fn() };

function panel() {
	return {
		api,
		content: { update: vi.fn() },
		dialog: { isOpen: false, query: {}, refresh: vi.fn() },
		upload: { open: vi.fn(), pick: vi.fn() },
		urls: { api: "/api" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(FilePickerField, {
		props: {
			endpoints: { field: "pages/test/fields/gallery" },
			name: "gallery",
			value: [],
			...props
		},
		attrs,
		global: {
			components: { "k-collection": Collection },
			mocks: { $events: events, $panel }
		}
	});
}

// the buttons render in the options slot of the k-field
function buttons(wrapper: ReturnType<typeof mount>) {
	return wrapper.vm.buttons;
}

describe("k-filepicker-field", () => {
	beforeEach(() => {
		api.get.mockResolvedValue([]);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-filepicker-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// computed
	describe("buttons computed", () => {
		it("offers only to select without uploads", () => {
			const wrapper = mount();
			expect(buttons(wrapper).map((button) => button.icon)).toStrictEqual([
				"checklist"
			]);
		});

		it("offers to upload before selecting", () => {
			const $panel = panel();
			const wrapper = mount({ autofocus: true, uploads: {} }, {}, $panel);
			const [upload, select] = buttons(wrapper);
			expect(upload).toMatchObject({
				autofocus: true,
				icon: "upload",
				text: "upload"
			});
			expect(select.icon).toBe("checklist");
			upload.click();
			expect($panel.upload.pick).toHaveBeenCalledWith(wrapper.vm.uploadOptions);
		});
	});

	describe("emptyProps computed", () => {
		function empty(props = {}) {
			const wrapper = mount(props);
			return wrapper.findComponent(Collection).props("empty");
		}

		it("asks for a single file by default", () => {
			expect(empty()).toStrictEqual({
				icon: "image",
				text: "field.files.empty.single"
			});
		});

		it("asks for files when multiple can be selected", () => {
			expect(empty({ multiple: true }).text).toBe("field.files.empty");
		});

		it("prefers the empty prop", () => {
			expect(empty({ empty: "No images yet" }).text).toBe("No images yet");
		});
	});

	describe("hasDropzone computed", () => {
		it("accepts dropped files with uploads", () => {
			const wrapper = mount({ uploads: {} });
			expect(wrapper.vm.hasDropzone).toBeTruthy();
			expect(wrapper.find("k-dropzone").attributes("disabled")).toBe("false");
		});

		it.each([
			["without uploads", {}],
			["when disabled", { disabled: true, uploads: {} }],
			["when full", { max: 1, uploads: {}, value: ["a"] }]
		])("accepts no dropped files %s", async (_, props) => {
			api.get.mockResolvedValue([{ id: "a" }]);
			const wrapper = mount(props);
			await flushPromises();
			expect(wrapper.vm.hasDropzone).toBeFalsy();
		});
	});

	describe("uploadOptions computed", () => {
		it("uploads to the field", () => {
			const wrapper = mount({
				max: 3,
				multiple: true,
				uploads: { accept: "image/*", preview: { ratio: "1/1" } }
			});
			expect(wrapper.vm.uploadOptions).toMatchObject({
				accept: "image/*",
				max: 3,
				multiple: true,
				preview: { ratio: "1/1" },
				url: "/api/pages/test/fields/gallery/upload"
			});
		});

		it("adds the uploaded files to the selection", async () => {
			const $panel = panel();
			api.get.mockResolvedValue([{ id: "a", uuid: "file://a" }]);
			const wrapper = mount(
				{ multiple: true, uploads: {}, value: ["file://a"] },
				{},
				$panel
			);
			await flushPromises();
			await wrapper.vm.uploadOptions.on.done([
				{ id: "a", uuid: "file://a" },
				{ id: "b", uuid: "file://b" }
			]);
			expect(wrapper.emitted("input")).toStrictEqual([
				[["file://a", "file://b"]]
			]);
			expect($panel.content.update).toHaveBeenCalledOnce();
		});

		it("replaces the selection for a single file", async () => {
			api.get.mockResolvedValue([{ id: "a", uuid: "file://a" }]);
			const wrapper = mount({
				multiple: false,
				uploads: {},
				value: ["file://a"]
			});
			await flushPromises();
			await wrapper.vm.uploadOptions.on.done([{ id: "b", uuid: "file://b" }]);
			expect(wrapper.emitted("input")).toStrictEqual([[["file://b"]]]);
		});

		it("refreshes an open picker dialog", async () => {
			const $panel = panel();
			$panel.dialog.isOpen = true;
			$panel.dialog.query = { page: 2 };
			const wrapper = mount({ uploads: {} }, {}, $panel);
			await wrapper.vm.uploadOptions.on.done([{ id: "b" }]);
			expect($panel.dialog.refresh).toHaveBeenCalledWith({
				query: { page: 2, value: ["b"] }
			});
		});
	});

	// lifecycle
	describe("mounted hook", () => {
		it("listens to deleted files until unmounted", () => {
			const wrapper = mount();
			const handler = wrapper.vm.onDelete;
			expect(events.on).toHaveBeenCalledWith("file.delete", handler);
			wrapper.unmount();
			expect(events.off).toHaveBeenCalledWith("file.delete", handler);
		});
	});

	// methods
	describe("onDelete()", () => {
		it("removes the deleted file by uuid", () => {
			const wrapper = mount({ value: ["file://a", "file://b"] });
			wrapper.vm.onDelete({
				event: "file.delete",
				file: { id: "test/a.jpg", uuid: "file://a" }
			});
			expect(wrapper.emitted("input")).toStrictEqual([[["file://b"]]]);
		});

		it("removes the deleted file by id", () => {
			const wrapper = mount({ value: ["test/a.jpg", "test/b.jpg"] });
			wrapper.vm.onDelete({
				event: "file.delete",
				file: { id: "test/a.jpg", uuid: null }
			});
			expect(wrapper.emitted("input")).toStrictEqual([[["test/b.jpg"]]]);
		});

		it("does not emit for files that are not selected", () => {
			const wrapper = mount({ value: ["file://a"] });
			wrapper.vm.onDelete({
				event: "file.delete",
				file: { id: "test/c.jpg", uuid: "file://c" }
			});
			expect(wrapper.emitted("input")).toBeUndefined();
		});

		it("does not emit without file in the response", () => {
			const wrapper = mount({ value: ["file://a"] });
			wrapper.vm.onDelete({ event: "file.delete", redirect: null });
			expect(wrapper.emitted("input")).toBeUndefined();
		});
	});

	describe("drop()", () => {
		it("uploads the dropped files", () => {
			const $panel = panel();
			const wrapper = mount({ uploads: {} }, {}, $panel);
			wrapper.vm.drop(["a.jpg"]);
			expect($panel.upload.open).toHaveBeenCalledWith(
				["a.jpg"],
				wrapper.vm.uploadOptions
			);
		});

		it("ignores dropped files when uploads are turned off", () => {
			const $panel = panel();
			const wrapper = mount({ uploads: false }, {}, $panel);
			expect(wrapper.vm.drop(["a.jpg"])).toBe(false);
			expect($panel.upload.open).not.toHaveBeenCalled();
		});
	});
});
