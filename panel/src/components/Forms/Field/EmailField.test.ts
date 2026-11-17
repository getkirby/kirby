import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import EmailField from "./EmailField.vue";

const Input = {
	name: "k-input",
	props: ["id", "placeholder", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: '<div><slot name="icon" /></div>'
};

function mount(props = {}, attrs = {}) {
	return vueMount(EmailField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

describe("k-email-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-email-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders an email k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("email");
		});
	});

	// props
	describe("icon prop", () => {
		it("defaults to email", () => {
			const wrapper = mount({ value: "mail@example.com" });
			expect(wrapper.find("k-button").attributes("icon")).toBe("email");
		});

		it("sets the icon of the button", () => {
			const wrapper = mount({ icon: "user", value: "mail@example.com" });
			expect(wrapper.find("k-button").attributes("icon")).toBe("user");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "email" });
			expect(wrapper.attributes("input")).toBe("email");
			expect(wrapper.findComponent(Input).props("id")).toBe("email");
		});
	});

	describe("link prop", () => {
		it("renders a button that opens the email", () => {
			const wrapper = mount({ value: "mail@example.com" });
			const button = wrapper.find("k-button");
			expect(button.attributes("link")).toBe("mailto:mail@example.com");
			expect(button.attributes("target")).toBe("_blank");
			expect(button.attributes("title")).toBe("open");
		});

		it("renders no button without value", () => {
			const wrapper = mount();
			expect(wrapper.find("k-button").exists()).toBe(false);
		});

		it("renders no button when turned off", () => {
			const wrapper = mount({ link: false, value: "mail@example.com" });
			expect(wrapper.find("k-button").exists()).toBe(false);
		});

		it("does not link the k-field label", () => {
			const wrapper = mount({ value: "mail@example.com" });
			expect(wrapper.attributes("link")).toBeUndefined();
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "mail@example.com" });
			expect(wrapper.findComponent(Input).props("value")).toBe(
				"mail@example.com"
			);
		});
	});

	// computed
	describe("mailto computed", () => {
		it("prefixes the value", () => {
			const wrapper = mount({ value: "mail@example.com" });
			expect(wrapper.vm.mailto).toBe("mailto:mail@example.com");
		});

		it("is null without value", () => {
			const wrapper = mount({ value: "" });
			expect(wrapper.vm.mailto).toBe(null);
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
			wrapper.findComponent(Input).vm.$emit("input", "mail@example.com");
			expect(wrapper.emitted("input")).toStrictEqual([["mail@example.com"]]);
		});
	});
});
