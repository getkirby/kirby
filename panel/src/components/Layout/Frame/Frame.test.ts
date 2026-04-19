import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import color from "@/helpers/color";
import Frame from "./Frame.vue";

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Frame, {
		props,
		attrs,
		slots,
		global: {
			mocks: {
				$helper: {
					color: (c: string) => c ?? null
				}
			}
		}
	});
}

describe("Frame.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-frame");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("back prop", () => {
		it("sets --back to the value returned by $helper.color", () => {
			const back = "blue-400";
			const wrapper = vueMount(Frame, {
				props: { back },
				global: { mocks: { $helper: { color } } }
			});
			expect(wrapper.attributes("style")).toContain(`--back: ${color(back)}`);
		});
	});

	describe("cover prop", () => {
		it("sets --fit to cover when true", () => {
			const wrapper = mount({ cover: true });
			expect(wrapper.attributes("style")).toContain("--fit: cover");
		});

		it("sets --fit to contain when false", () => {
			const wrapper = mount({ cover: false });
			expect(wrapper.attributes("style")).toContain("--fit: contain");
		});

		it("is overridden by explicit fit prop", () => {
			const wrapper = mount({ cover: true, fit: "fill" });
			expect(wrapper.attributes("style")).toContain("--fit: fill");
		});
	});

	describe("element prop", () => {
		it("defaults to a div element", () => {
			const wrapper = mount();
			expect(wrapper.element.tagName).toBe("DIV");
		});

		it("renders as the specified HTML element", () => {
			const wrapper = mount({ element: "figure" });
			expect(wrapper.element.tagName).toBe("FIGURE");
		});
	});

	describe("fit prop", () => {
		it("sets the CSS custom property", () => {
			const wrapper = mount({ fit: "fill" });
			expect(wrapper.attributes("style")).toContain("--fit: fill");
		});
	});

	describe("ratio prop", () => {
		it("renders value as data-ratio attribute", () => {
			const wrapper = mount({ ratio: "16/9" });
			expect(wrapper.attributes("data-ratio")).toBe("16/9");
		});

		it("sets the CSS custom property", () => {
			const wrapper = mount({ ratio: "4/3" });
			expect(wrapper.attributes("style")).toContain("--ratio: 4/3");
		});
	});

	describe("theme prop", () => {
		it("renders value as data-theme attribute", () => {
			const wrapper = mount({ theme: "positive" });
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content as direct child", () => {
			const slot = '<img src="test.jpg">';
			const wrapper = mount({}, {}, { default: slot });
			expect(wrapper.element.innerHTML).toBe(slot);
		});
	});
});
