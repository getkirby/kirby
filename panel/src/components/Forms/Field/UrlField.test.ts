import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import UrlField from "./UrlField.vue";

const Input = {
	name: "k-input",
	props: ["id", "placeholder", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: '<div><slot name="icon" /></div>'
};

function mount(props = {}, attrs = {}) {
	return vueMount(UrlField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

describe("k-url-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-url-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a url k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("url");
		});
	});

	// props
	describe("icon prop", () => {
		it("defaults to url", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			expect(wrapper.find("k-button").attributes("icon")).toBe("url");
		});

		it("sets the icon of the button", () => {
			const wrapper = mount({ icon: "globe", value: "https://getkirby.com" });
			expect(wrapper.find("k-button").attributes("icon")).toBe("globe");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "website" });
			expect(wrapper.attributes("input")).toBe("website");
			expect(wrapper.findComponent(Input).props("id")).toBe("website");
		});
	});

	describe("link prop", () => {
		it("renders a button that opens the URL", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			const button = wrapper.find("k-button");
			expect(button.attributes("link")).toBe("https://getkirby.com");
			expect(button.attributes("target")).toBe("_blank");
			expect(button.attributes("title")).toBe("open");
		});

		it("renders no button for an invalid URL", () => {
			const wrapper = mount({ value: "https://getkirby" });
			expect(wrapper.find("k-button").exists()).toBe(false);
		});

		it("renders no button when turned off", () => {
			const wrapper = mount({ link: false, value: "https://getkirby.com" });
			expect(wrapper.find("k-button").exists()).toBe(false);
		});

		it("does not link the k-field label", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			expect(wrapper.attributes("link")).toBeUndefined();
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			expect(wrapper.findComponent(Input).props("value")).toBe(
				"https://getkirby.com"
			);
		});
	});

	// computed
	describe("isValidUrl computed", () => {
		it("accepts a valid URL", () => {
			const wrapper = mount({ value: "https://getkirby.com" });
			expect(wrapper.vm.isValidUrl).toBe(true);
		});

		it("rejects a URL that fails the strict check", () => {
			const wrapper = mount({ value: "https://getkirby" });
			expect(wrapper.vm.isValidUrl).toBe(false);
		});

		it("rejects an empty value", () => {
			const wrapper = mount({ value: "" });
			expect(wrapper.vm.isValidUrl).toBe(false);
		});

		it("rejects a missing value", () => {
			const wrapper = mount();
			expect(wrapper.vm.isValidUrl).toBe(false);
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
			wrapper.findComponent(Input).vm.$emit("input", "https://getkirby.com");
			expect(wrapper.emitted("input")).toStrictEqual([
				["https://getkirby.com"]
			]);
		});
	});
});
