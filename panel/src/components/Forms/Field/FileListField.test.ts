import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import FileListField from "./FileListField.vue";

// registered globally in the Panel
const Collection = { props: ["items"], template: "<div />" };

const api = { get: vi.fn(), patch: vi.fn() };
const events = { emit: vi.fn(), off: vi.fn(), on: vi.fn() };

const upload = { api: "pages/test/files", template: "image" };
const state = {
	models: [],
	pagination: { limit: 20, offset: 0, page: 1, total: 0 },
	sortable: true,
	upload
};

function panel(create = true) {
	return {
		error: vi.fn(),
		events: { emit: vi.fn() },
		notification: { success: vi.fn() },
		permissions: { files: { create } },
		upload: { open: vi.fn(), pick: vi.fn(), replace: vi.fn() },
		urls: { api: "/api" }
	};
}

function mount(props = {}, attrs = {}, $panel = panel()) {
	return vueMount(FileListField, {
		props: {
			endpoints: { field: "pages/test/fields/gallery" },
			name: "gallery",
			...props
		},
		attrs,
		global: {
			components: { "k-collection": Collection },
			mocks: {
				$api: api,
				$dropdown: () => ({}),
				$events: events,
				$panel
			}
		}
	});
}

describe("k-filelist-field", () => {
	beforeEach(() => {
		api.get.mockResolvedValue(state);
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-modellist-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("passes the type to the k-field", () => {
			const wrapper = mount({ type: "files" });
			expect(wrapper.attributes("type")).toBe("files");
		});
	});

	// computed
	describe("addButton computed", () => {
		it("offers to upload", () => {
			const wrapper = mount();
			expect(wrapper.vm.addButton).toMatchObject({
				icon: "upload",
				text: "add"
			});
		});
	});

	describe("canAdd computed", () => {
		it("can add files with uploads and permission", async () => {
			const wrapper = mount();
			await flushPromises();
			expect(wrapper.vm.canAdd).toBe(true);
			expect(wrapper.vm.canDrop).toBe(true);
		});

		it("cannot add files without permission", async () => {
			const wrapper = mount({}, {}, panel(false));
			await flushPromises();
			expect(wrapper.vm.canAdd).toBe(false);
			expect(wrapper.vm.canDrop).toBe(false);
		});

		it("cannot add files without uploads", async () => {
			api.get.mockResolvedValue({ ...state, upload: false });
			const wrapper = mount();
			await flushPromises();
			expect(wrapper.vm.canAdd).toBe(false);
		});
	});

	describe("icon computed", () => {
		it("is image", () => {
			const wrapper = mount();
			expect(wrapper.vm.icon).toBe("image");
		});
	});

	describe("uploadOptions computed", () => {
		it("uploads to the api of the field", async () => {
			const wrapper = mount();
			await flushPromises();
			expect(wrapper.vm.uploadOptions).toStrictEqual({
				...upload,
				url: "/api/pages/test/files"
			});
		});

		it("is null without uploads", async () => {
			api.get.mockResolvedValue({ ...state, upload: false });
			const wrapper = mount();
			await flushPromises();
			expect(wrapper.vm.uploadOptions).toBe(null);
		});
	});

	// methods
	describe("onAction()", () => {
		it("replaces a file", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onAction("replace", { id: "a" });
			expect($panel.upload.replace).toHaveBeenCalledWith(
				{ id: "a" },
				wrapper.vm.uploadOptions
			);
		});

		it("ignores other actions", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onAction("delete", { id: "a" });
			expect($panel.upload.replace).not.toHaveBeenCalled();
		});
	});

	describe("onAdd()", () => {
		it("opens the file picker for uploads", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onAdd();
			expect($panel.upload.pick).toHaveBeenCalledWith(wrapper.vm.uploadOptions);
		});

		it("opens nothing without permission", async () => {
			const $panel = panel(false);
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onAdd();
			expect($panel.upload.pick).not.toHaveBeenCalled();
		});
	});

	describe("onDrop()", () => {
		it("uploads the dropped files", async () => {
			const $panel = panel();
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onDrop(["a.jpg"]);
			expect($panel.upload.open).toHaveBeenCalledWith(
				["a.jpg"],
				wrapper.vm.uploadOptions
			);
		});

		it("ignores dropped files without permission", async () => {
			const $panel = panel(false);
			const wrapper = mount({}, {}, $panel);
			await flushPromises();
			wrapper.vm.onDrop(["a.jpg"]);
			expect($panel.upload.open).not.toHaveBeenCalled();
		});
	});

	describe("onSort()", () => {
		it("sorts the files from the offset of the page", async () => {
			api.get.mockResolvedValue({
				...state,
				pagination: { ...state.pagination, offset: 20 }
			});
			const wrapper = mount();
			await flushPromises();
			await wrapper.vm.onSort([{ id: "b" }, { id: "a" }]);
			expect(api.patch).toHaveBeenCalledWith("pages/test/fields/gallery/sort", {
				files: ["b", "a"],
				index: 20
			});
		});

		it("does not sort an unsortable list", async () => {
			api.get.mockResolvedValue({ ...state, sortable: false });
			const wrapper = mount();
			await flushPromises();
			expect(await wrapper.vm.onSort([{ id: "a" }])).toBe(false);
			expect(api.patch).not.toHaveBeenCalled();
		});
	});

	describe("refreshEvents()", () => {
		it("reloads the files after file changes", async () => {
			const wrapper = mount();
			await flushPromises();
			for (const event of wrapper.vm.refreshEvents()) {
				expect(events.on).toHaveBeenCalledWith(event, wrapper.vm.onRefresh);
			}
			expect(wrapper.vm.refreshEvents()).toContain("file.delete");
		});
	});
});
