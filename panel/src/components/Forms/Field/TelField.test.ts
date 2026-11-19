import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TelField from "./TelField.vue";

const Input = {
	name: "k-input",
	props: ["icon", "id", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(TelField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

describe("k-tel-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-tel-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a tel k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("tel");
		});
	});

	// props
	describe("icon prop", () => {
		it("defaults to phone", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("icon")).toBe("phone");
		});

		it("passes the icon to the k-input", () => {
			const wrapper = mount({ icon: "mobile" });
			expect(wrapper.findComponent(Input).props("icon")).toBe("mobile");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "phone" });
			expect(wrapper.attributes("input")).toBe("phone");
			expect(wrapper.findComponent(Input).props("id")).toBe("phone");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "+49 30 1234567" });
			expect(wrapper.findComponent(Input).props("value")).toBe(
				"+49 30 1234567"
			);
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the k-input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Input.methods.focus).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "+49 30 1234567");
			expect(wrapper.emitted("input")).toStrictEqual([["+49 30 1234567"]]);
		});
	});
});
