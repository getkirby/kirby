import { describe, expect, it, vi } from "@test/unit";
import {
	mount as vueMount,
	type ComponentMountingOptions
} from "@vue/test-utils";
import { h } from "vue";
import ErrorBoundary from "./ErrorBoundary.vue";

type Slots = ComponentMountingOptions<typeof ErrorBoundary>["slots"];

/**
 * A component that fails to render
 */
function Broken(): never {
	throw new Error("Broken");
}

/**
 * Mounts the boundary around a component that fails to render
 */
async function crash(slots: Slots = {}, $panel = { debug: false }) {
	const wrapper = mount({}, { default: () => h(Broken), ...slots }, $panel);
	await wrapper.vm.$nextTick();
	return wrapper;
}

function mount(
	attrs = {},
	slots: Slots = { default: () => h("p", "Content") },
	$panel = { debug: false }
) {
	return vueMount(ErrorBoundary, {
		attrs,
		slots,
		global: { mocks: { $panel } }
	});
}

describe("ErrorBoundary.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// hooks
	describe("errorCaptured hook", () => {
		it("replaces the content with a negative box", async () => {
			const wrapper = await crash();
			const box = wrapper.find("k-box");

			expect(wrapper.find("p").exists()).toBe(false);
			expect(box.attributes("text")).toBe("Broken");
			expect(box.attributes("theme")).toBe("negative");
		});

		it("shows an error without message as text", async () => {
			const wrapper = mount(
				{},
				{
					default: () =>
						h(() => {
							throw "Broken";
						})
				}
			);
			await wrapper.vm.$nextTick();

			expect(wrapper.find("k-box").attributes("text")).toBe("Broken");
		});

		it("stops the error from propagating", async () => {
			const errorCaptured = vi.fn();
			const wrapper = vueMount(
				{
					errorCaptured,
					render: () => h(ErrorBoundary, null, { default: () => h(Broken) })
				},
				{ global: { mocks: { $panel: { debug: false } } } }
			);
			await wrapper.vm.$nextTick();

			expect(errorCaptured).not.toHaveBeenCalled();
		});

		it("logs the error in debug mode", async () => {
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			await crash({}, { debug: true });

			expect(warn).toHaveBeenCalledWith(new Error("Broken"));
			warn.mockRestore();
		});

		it("does not log the error outside debug mode", async () => {
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			await crash();

			expect(warn).not.toHaveBeenCalled();
			warn.mockRestore();
		});
	});

	// slots
	describe("default slot", () => {
		it("renders its content", () => {
			expect(mount().text()).toBe("Content");
		});

		it("renders nothing without content", () => {
			expect(mount({}, {}).html()).toBe("");
		});
	});

	describe("error slot", () => {
		it("is not rendered without an error", () => {
			const wrapper = mount(
				{},
				{
					default: () => h("p", "Content"),
					error: () => h("p", "Caught")
				}
			);
			expect(wrapper.text()).toBe("Content");
		});

		it("replaces the negative box", async () => {
			const wrapper = await crash({ error: () => h("p", "Caught") });
			expect(wrapper.find("k-box").exists()).toBe(false);
			expect(wrapper.text()).toBe("Caught");
		});

		it("receives the error", async () => {
			const wrapper = await crash({
				error: ({ error }: { error: Error }) =>
					h("p", "Caught: " + error.message)
			});
			expect(wrapper.text()).toBe("Caught: Broken");
		});
	});
});
