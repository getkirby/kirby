import { describe, it, expect } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Empty from "./Empty.vue";

function mount(props: Record<string, unknown> = {}, attrs: Record<string, unknown> = {}) {
	return vueMount(Empty, {
		props,
		attrs,
		shallow: true
	});
}

describe("Empty.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs?: Record<string, unknown>) => mount({}, attrs);

		it.rendersAs(component, "K-BOX", "k-empty");
		it.acceptsClass(component);
		it.acceptsStyle(component);
	});

	// props
	describe("text prop", () => {
		it("renders text content", () => {
			const wrapper = mount({ text: "No items yet" });
			expect(wrapper.text()).toBe("No items yet");
		});
	});

	describe("icon prop", () => {
		it("passes to k-box as icon attribute", () => {
			const wrapper = mount({ icon: "image" });
			expect(wrapper.attributes("icon")).toBe("image");
		});
	});

	describe("layout prop", () => {
		it("sets align to center in cards layout", () => {
			const wrapper = mount({ layout: "cards" });
			expect(wrapper.attributes("align")).toBe("center");
		});

		it("sets align to center in cardlets layout", () => {
			const wrapper = mount({ layout: "cardlets" });
			expect(wrapper.attributes("align")).toBe("center");
		});

		it("does not set align in list layout", () => {
			const wrapper = mount({ layout: "list" });
			expect(wrapper.attributes("align")).toBeUndefined();
		});

		it("sets a height in cards layout", () => {
			const wrapper = mount({ layout: "cards" });
			expect(wrapper.attributes("height")).toBeDefined();
		});

		it("sets a height in cardlets layout", () => {
			const wrapper = mount({ layout: "cardlets" });
			expect(wrapper.attributes("height")).toBeDefined();
		});

		it("does not set a height in list layout", () => {
			const wrapper = mount({ layout: "list" });
			expect(wrapper.attributes("height")).toBeUndefined();
		});
	});

	// computed: attrs
	describe("attrs", () => {
		it("always sets theme to empty", () => {
			const wrapper = mount();
			expect(wrapper.attributes("theme")).toBe("empty");
		});

		it("sets button to true when onClick is provided", () => {
			const wrapper = mount({ onClick: () => {} });
			expect(wrapper.attributes("button")).toBe("true");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content", () => {
			const wrapper = vueMount(Empty, {
				slots: { default: "<span>Custom empty message</span>" },
				shallow: true
			});
			expect(wrapper.find("span").text()).toBe("Custom empty message");
		});

		it("prefers slot content over text prop", () => {
			const wrapper = vueMount(Empty, {
				props: { text: "fallback text" },
				slots: { default: "Slot content" },
				shallow: true
			});
			expect(wrapper.text()).toBe("Slot content");
		});
	});

	// events
	describe("click event", () => {
		it("emits click when clicked", async () => {
			const wrapper = mount({ onClick: () => {} });
			await wrapper.trigger("click");
			expect(wrapper.emitted("click")).toBeTruthy();
		});
	});
});
