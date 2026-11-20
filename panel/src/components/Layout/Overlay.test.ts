import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Overlay from "./Overlay.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(Overlay, {
		props,
		attrs,
		slots: { default: `<div class="k-portal"><p>Content</p></div>` },
		attachTo: document.body
	});
}

function dialog(wrapper: ReturnType<typeof mount>) {
	return wrapper.element as HTMLDialogElement;
}

// open() emits and focuses after a timeout
async function tick() {
	await new Promise((resolve) => setTimeout(resolve));
}

describe("k-overlay", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIALOG", "k-overlay");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is an overlay by default", () => {
			expect(mount().attributes("data-type")).toBe("overlay");
		});
	});

	// props
	describe("autofocus prop", () => {
		it("focuses the overlay when opened", async () => {
			const wrapper = mount({ visible: true });
			const focus = vi.spyOn(wrapper.vm.$helper, "focus");
			onTestFinished(() => focus.mockRestore());
			await tick();
			expect(focus).toHaveBeenCalledWith(dialog(wrapper));
		});

		it("doesn't focus without autofocus", async () => {
			const focus = vi.spyOn(mount().vm.$helper, "focus");
			onTestFinished(() => focus.mockRestore());
			mount({ autofocus: false, visible: true });
			await tick();
			expect(focus).not.toHaveBeenCalled();
		});

		it("overrides the focus that showModal() sets on the first button", async () => {
			// browsers focus the first focusable element in showModal()
			const showModal = HTMLDialogElement.prototype.showModal;
			const spy = vi
				.spyOn(HTMLDialogElement.prototype, "showModal")
				.mockImplementation(function (this: HTMLDialogElement) {
					showModal.call(this);
					this.querySelector("button")?.focus();
				});
			onTestFinished(() => spy.mockRestore());

			const wrapper = vueMount(Overlay, {
				props: { visible: true },
				slots: {
					default: `<form><button type="button">Cancel</button><button type="submit">Delete</button></form>`
				},
				attachTo: document.body
			});
			await tick();

			expect(document.activeElement).toBe(
				wrapper.find("[type=submit]").element
			);
		});
	});

	describe("nested prop", () => {
		it("only emits close for a nested overlay", () => {
			const wrapper = mount({ nested: true, visible: true });
			wrapper.vm.close();
			expect(wrapper.emitted("close")).toHaveLength(1);
			expect(dialog(wrapper).open).toBe(true);
		});

		it("cancels a nested overlay on escape", () => {
			const wrapper = mount({ nested: true, visible: true });
			const event = new Event("cancel", { cancelable: true });
			dialog(wrapper).dispatchEvent(event);
			expect(event.defaultPrevented).toBe(true);
			expect(wrapper.emitted("cancel")).toHaveLength(1);
			expect(wrapper.emitted("close")).toHaveLength(1);
			expect(dialog(wrapper).open).toBe(true);
		});

		it("leaves escape to the browser otherwise", () => {
			const wrapper = mount({ visible: true });
			const event = new Event("cancel", { cancelable: true });
			dialog(wrapper).dispatchEvent(event);
			expect(event.defaultPrevented).toBe(false);
			expect(wrapper.emitted("cancel")).toBeUndefined();
		});
	});

	describe("type prop", () => {
		it("sets the type", () => {
			expect(mount({ type: "drawer" }).attributes("data-type")).toBe("drawer");
		});
	});

	describe("visible prop", () => {
		it("is closed by default", () => {
			expect(dialog(mount()).open).toBe(false);
		});

		it("opens the overlay on creation", async () => {
			const wrapper = mount({ visible: true });
			expect(dialog(wrapper).open).toBe(true);
			await tick();
			expect(wrapper.emitted("open")).toHaveLength(1);
		});
	});

	// watch
	describe("visible watcher", () => {
		it("opens and closes the overlay", async () => {
			const wrapper = mount();
			await wrapper.setProps({ visible: true } as never);
			expect(dialog(wrapper).open).toBe(true);
			await wrapper.setProps({ visible: false } as never);
			expect(dialog(wrapper).open).toBe(false);
			expect(wrapper.emitted("close")).toHaveLength(1);
		});
	});

	// methods
	describe("cancel()", () => {
		it("emits cancel and closes the overlay", () => {
			const wrapper = mount({ visible: true });
			wrapper.vm.cancel();
			expect(wrapper.emitted("cancel")).toHaveLength(1);
			expect(wrapper.emitted("close")).toHaveLength(1);
			expect(dialog(wrapper).open).toBe(false);
		});
	});

	describe("close()", () => {
		it("does nothing for a closed overlay", () => {
			// a nested overlay would emit close
			const wrapper = mount({ nested: true });
			wrapper.vm.close();
			expect(wrapper.emitted("close")).toBeUndefined();
		});
	});

	describe("open()", () => {
		it("opens the overlay once", async () => {
			const wrapper = mount({ visible: true });
			const showModal = vi.spyOn(dialog(wrapper), "showModal");
			wrapper.vm.open();
			expect(showModal).not.toHaveBeenCalled();
			await tick();
			expect(wrapper.emitted("open")).toHaveLength(2);
		});
	});

	// events
	describe("events", () => {
		it("cancels on a press on the backdrop", async () => {
			const wrapper = mount({ visible: true });
			await wrapper.find(".k-portal").trigger("mousedown");
			expect(wrapper.emitted("cancel")).toHaveLength(1);
			expect(dialog(wrapper).open).toBe(false);
		});

		it("stays open on a press on the content", async () => {
			const wrapper = mount({ visible: true });
			await wrapper.find("p").trigger("mousedown");
			expect(wrapper.emitted("cancel")).toBeUndefined();
			expect(dialog(wrapper).open).toBe(true);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content", () => {
			expect(mount().find(".k-portal p").text()).toBe("Content");
		});
	});
});
