import { describe, expect, it, onTestFinished, vi } from "@test/unit";
import {
	mount as vueMount,
	type ComponentMountingOptions
} from "@vue/test-utils";
import { h, ref } from "vue";
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

describe("k-error-boundary", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "P");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// lifecycle
	describe("errorCaptured hook", () => {
		it("replaces the content with a negative box", async () => {
			const broken = ref(false);
			const Fragile = () => {
				if (broken.value === true) {
					throw new Error("Broken");
				}

				return h("span", "Fragile");
			};

			// only the fragile part fails, the whole content must go
			const wrapper = mount(
				{},
				{
					default: () => h("div", [h("p", "Content"), h(Fragile)])
				}
			);
			expect(wrapper.find("p").exists()).toBe(true);

			broken.value = true;
			await wrapper.vm.$nextTick();
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
			onTestFinished(() => warn.mockRestore());
			await crash({}, { debug: true });

			expect(warn).toHaveBeenCalledWith(new Error("Broken"));
		});

		it("does not log the error outside debug mode", async () => {
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			onTestFinished(() => warn.mockRestore());
			await crash();

			expect(warn).not.toHaveBeenCalled();
		});
	});

	// slots
	describe("default slot", () => {
		it("renders its content", () => {
			const wrapper = mount();
			expect(wrapper.text()).toBe("Content");
		});

		it("renders nothing without content", () => {
			const wrapper = mount({}, {});
			expect(wrapper.html()).toBe("");
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
