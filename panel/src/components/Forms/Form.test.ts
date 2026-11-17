import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Form from "./Form.vue";

const Fieldset = {
	name: "k-fieldset",
	props: ["disabled", "fields", "value"],
	emits: ["focus", "input", "submit"],
	methods: { focus: vi.fn() },
	template: "<div />"
};
const fields = { title: { type: "text" } };

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Form, {
		props: { fields, value: { title: "A" }, ...props },
		attrs,
		slots,
		// the hidden submit button only works in a connected form
		attachTo: document.body,
		global: {
			stubs: { "k-fieldset": Fieldset }
		}
	});
}

describe("k-form", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FORM", "k-form");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is a post form without autocomplete", () => {
			const wrapper = mount();
			expect(wrapper.attributes("method")).toBe("POST");
			expect(wrapper.attributes("autocomplete")).toBe("off");
		});

		it("has a hidden submit button", () => {
			const wrapper = mount();
			const submitter = wrapper.find(".k-form-submitter");
			expect(submitter.attributes("type")).toBe("submit");
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the k-fieldset", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.findComponent(Fieldset).props("disabled")).toBe(true);
		});
	});

	describe("fields prop", () => {
		it("passes the fields and values to the k-fieldset", () => {
			const wrapper = mount();
			const fieldset = wrapper.findComponent(Fieldset);
			expect(fieldset.props("fields")).toStrictEqual(fields);
			expect(fieldset.props("value")).toStrictEqual({ title: "A" });
		});
	});

	describe("novalidate prop", () => {
		it("validates by default", () => {
			const wrapper = mount();
			expect(wrapper.attributes("novalidate")).toBeUndefined();
		});

		it("turns off the native validation", () => {
			const wrapper = mount({ novalidate: true });
			expect(wrapper.element.noValidate).toBe(true);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the field in the k-fieldset", () => {
			const wrapper = mount();
			wrapper.vm.focus("title");
			expect(Fieldset.methods.focus).toHaveBeenCalledWith("title");
		});
	});

	describe("submit()", () => {
		it("submits the form", () => {
			const wrapper = mount();
			wrapper.vm.submit();
			expect(wrapper.emitted("submit")).toStrictEqual([[{ title: "A" }]]);
		});
	});

	// events
	describe("focus event", () => {
		it("emits the focus of a field", () => {
			const wrapper = mount();
			const event = new FocusEvent("focus");
			wrapper
				.findComponent(Fieldset)
				.vm.$emit("focus", event, fields.title, "title");
			expect(wrapper.emitted("focus")).toStrictEqual([
				[event, fields.title, "title"]
			]);
		});
	});

	describe("input event", () => {
		it("emits the values of the k-fieldset", () => {
			const wrapper = mount();
			wrapper
				.findComponent(Fieldset)
				.vm.$emit("input", { title: "B" }, fields.title, "title");
			expect(wrapper.emitted("input")).toStrictEqual([
				[{ title: "B" }, fields.title, "title"]
			]);
		});
	});

	describe("submit event", () => {
		it("emits the values instead of submitting the page", async () => {
			const wrapper = mount();
			const event = new Event("submit", { cancelable: true });
			wrapper.element.dispatchEvent(event);
			expect(event.defaultPrevented).toBe(true);
			expect(wrapper.emitted("submit")).toStrictEqual([[{ title: "A" }]]);
		});

		it("emits the values for a submit in a field", () => {
			const wrapper = mount();
			wrapper.findComponent(Fieldset).vm.$emit("submit");
			expect(wrapper.emitted("submit")).toStrictEqual([[{ title: "A" }]]);
		});
	});

	// slots
	describe("default slot", () => {
		it("replaces the k-fieldset", () => {
			const wrapper = mount({}, {}, { default: "<p class='custom' />" });
			expect(wrapper.findComponent(Fieldset).exists()).toBe(false);
			expect(wrapper.find(".custom").exists()).toBe(true);
		});
	});

	describe("footer slot", () => {
		it("renders after the fields", () => {
			const wrapper = mount({}, {}, { footer: "<p class='custom' />" });
			const children = [...wrapper.element.children];
			expect(children.findIndex((el) => el.className === "custom")).toBe(1);
		});
	});

	describe("header slot", () => {
		it("renders before the fields", () => {
			const wrapper = mount({}, {}, { header: "<p class='custom' />" });
			expect(wrapper.element.firstElementChild?.className).toBe("custom");
		});
	});
});
