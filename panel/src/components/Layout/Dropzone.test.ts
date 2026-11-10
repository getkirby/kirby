import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Dropzone from "./Dropzone.vue";

const events = { emit: vi.fn() };

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Dropzone, {
		props,
		attrs,
		slots,
		global: {
			mocks: { $events: events }
		}
	});
}

/**
 * Event data of dragging files
 */
function files(list: File[] = []) {
	return {
		dataTransfer: { dropEffect: "none", files: list, types: ["Files"] }
	};
}

/**
 * Event data of dragging text, e.g. a sortable item
 */
function text() {
	return {
		dataTransfer: { dropEffect: "none", files: [], types: ["text/plain"] }
	};
}

describe("k-dropzone", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-dropzone");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("sets data-dragging to false", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});

		it("sets data-over to false", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-over")).toBe("false");
		});
	});

	// events
	describe("dragenter event", () => {
		it("sets data-dragging to true", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter", files());
			expect(wrapper.attributes("data-dragging")).toBe("true");
		});

		it("does not set data-dragging when disabled", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.trigger("dragenter", files());
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});

		it("does not set data-dragging without files", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter", text());
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});
	});

	describe("dragleave event", () => {
		it("resets data-dragging to false", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter", files());
			await wrapper.trigger("dragleave");
			expect(wrapper.attributes("data-dragging")).toBe("false");
		});
	});

	describe("dragover event", () => {
		it("sets data-over to true and allows copying", async () => {
			const wrapper = mount();
			const data = files();
			await wrapper.trigger("dragover", data);

			expect(wrapper.attributes("data-over")).toBe("true");
			expect(data.dataTransfer.dropEffect).toBe("copy");
		});

		it("does not set data-over when disabled", async () => {
			const wrapper = mount({ disabled: true });
			const data = files();
			await wrapper.trigger("dragover", data);

			expect(wrapper.attributes("data-over")).toBe("false");
			expect(data.dataTransfer.dropEffect).toBe("none");
		});

		it("does not set data-over without files", async () => {
			const wrapper = mount();
			const data = text();
			await wrapper.trigger("dragover", data);

			expect(wrapper.attributes("data-over")).toBe("false");
			expect(data.dataTransfer.dropEffect).toBe("none");
		});
	});

	describe("drop event", () => {
		it("emits drop with the dropped files", async () => {
			const list = [new File([""], "test.txt")];
			const wrapper = mount();
			await wrapper.trigger("drop", files(list));

			expect(wrapper.emitted("drop")).toStrictEqual([[list]]);
			expect(events.emit).toHaveBeenCalledWith("dropzone.drop");
		});

		it("does not emit drop when disabled", async () => {
			const wrapper = mount({ disabled: true });
			await wrapper.trigger("drop", files());

			expect(wrapper.emitted("drop")).toBeUndefined();
			expect(events.emit).not.toHaveBeenCalled();
		});

		it("does not emit drop without files", async () => {
			const wrapper = mount();
			await wrapper.trigger("drop", text());

			expect(wrapper.emitted("drop")).toBeUndefined();
			expect(events.emit).not.toHaveBeenCalled();
		});

		it("resets dragging and over state after drop", async () => {
			const wrapper = mount();
			await wrapper.trigger("dragenter", files());
			await wrapper.trigger("dragover", files());
			await wrapper.trigger("drop", files());

			expect(wrapper.attributes("data-dragging")).toBe("false");
			expect(wrapper.attributes("data-over")).toBe("false");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = mount({}, {}, { default: "<p>Drop here</p>" });
			expect(wrapper.find("p").text()).toBe("Drop here");
		});
	});
});
