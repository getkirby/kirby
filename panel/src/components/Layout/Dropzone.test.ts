import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Dropzone from "./Dropzone.vue";

type DropzoneInstance = { $events: { emit: ReturnType<typeof vi.fn> } };

function mount(props = {}, attrs = {}) {
	return vueMount(Dropzone, {
		props,
		attrs,
		global: {
			mocks: {
				$helper: { isUploadEvent: () => true },
				$events: { emit: vi.fn() }
			}
		}
	});
}

describe("Dropzone.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-dropzone");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// data
	describe("initial state", () => {
		it("sets data-dragging to false", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});

		it("sets data-over to false", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-over")).toBe("false");
		});
	});

	// drag events
	describe("dragenter event", () => {
		it("sets data-dragging to true", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter");
			expect(wrapper.attributes("data-dragging")).toBe("true");
		});

		it("does not set data-dragging when disabled", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.trigger("dragenter");
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});
	});

	describe("dragleave event", () => {
		it("resets data-dragging to false", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter");
			await wrapper.trigger("dragleave");
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});
	});

	describe("dragover event", () => {
		it("sets data-over to true", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragover", {
				dataTransfer: { dropEffect: "" }
			});
			expect(wrapper.attributes("data-over")).toBe("true");
		});

		it("does not set data-over when disabled", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.trigger("dragover", {
				dataTransfer: { dropEffect: "" }
			});
			expect(wrapper.attributes("data-over")).toBe("false");
		});
	});

	describe("drop event", () => {
		it("emits drop with the dropped files", async () => {
			const files = [new File([""], "test.txt")];
			const wrapper = mount();
			await wrapper.trigger("drop", {
				dataTransfer: { files }
			});
			expect(wrapper.emitted("drop")?.[0][0]).toEqual(files);
			expect((wrapper.vm as unknown as DropzoneInstance).$events.emit).toHaveBeenCalledWith(
				"dropzone.drop"
			);
		});

		it("does not emit drop when disabled", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.trigger("drop", {
				dataTransfer: { files: [] }
			});
			expect(wrapper.emitted("drop")).toBeUndefined();
			expect((wrapper.vm as unknown as DropzoneInstance).$events.emit).not.toHaveBeenCalled();
		});

		it("does not emit drop when event is not an upload event", async () => {
			const wrapper = vueMount(Dropzone, {
				global: {
					mocks: {
						$helper: { isUploadEvent: () => false },
						$events: { emit: vi.fn() }
					}
				}
			});
			await wrapper.trigger("drop", { dataTransfer: { files: [] } });
			expect(wrapper.emitted("drop")).toBeUndefined();
			expect((wrapper.vm as unknown as DropzoneInstance).$events.emit).not.toHaveBeenCalled();
		});

		it("resets dragging and over state after drop", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter");
			await wrapper.trigger("dragover", { dataTransfer: { dropEffect: "" } });
			await wrapper.trigger("drop", { dataTransfer: { files: [] } });
			expect(wrapper.attributes("data-dragging")).toBe("false");
			expect(wrapper.attributes("data-over")).toBe("false");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = vueMount(Dropzone, {
				slots: { default: "<p>Drop here</p>" },
				global: {
					mocks: {
						$helper: { isUploadEvent: () => true },
						$events: { emit: () => {} }
					}
				}
			});
			expect(wrapper.find("p").text()).toBe("Drop here");
		});
	});
});
