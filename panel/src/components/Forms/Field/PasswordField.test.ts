import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import PasswordField from "./PasswordField.vue";

const Field = {
	name: "k-field",
	props: ["counter", "input"],
	template: `<div>
		<div v-if="$slots.options" class="options"><slot name="options" /></div>
		<slot />
	</div>`
};
const Input = {
	name: "k-input",
	props: ["icon", "id", "minlength", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn() },
	template: "<input />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(PasswordField, {
		props,
		attrs,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

// a literal <k-field> tag only renders the default slot
function field(props = {}, slots = {}) {
	return vueMount(PasswordField, {
		props,
		slots,
		global: {
			stubs: { "k-field": Field, "k-input": Input }
		}
	});
}

describe("k-password-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-password-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a password k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("password");
		});
	});

	// props
	describe("counter prop", () => {
		it("passes the counter options with the minimum to the k-field", () => {
			const wrapper = field({ value: "abc" });
			expect(wrapper.findComponent(Field).props("counter")).toStrictEqual({
				count: 3,
				max: undefined,
				min: 8
			});
		});

		it("passes no counter when hidden", () => {
			const wrapper = field({ counter: false, value: "abc" });
			expect(wrapper.findComponent(Field).props("counter")).toBe(false);
		});
	});

	describe("icon prop", () => {
		it("defaults to key", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("icon")).toBe("key");
		});

		it("passes the icon to the k-input", () => {
			const wrapper = mount({ icon: "lock" });
			expect(wrapper.findComponent(Input).props("icon")).toBe("lock");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = field({ id: "password" });
			expect(wrapper.findComponent(Field).props("input")).toBe("password");
			expect(wrapper.findComponent(Input).props("id")).toBe("password");
		});
	});

	describe("minlength prop", () => {
		it("defaults to 8", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("minlength")).toBe(8);
		});

		it("passes the minimum length to the k-input", () => {
			const wrapper = mount({ minlength: 12 });
			expect(wrapper.findComponent(Input).props("minlength")).toBe(12);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "topsecret" });
			expect(wrapper.findComponent(Input).props("value")).toBe("topsecret");
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
			wrapper.findComponent(Input).vm.$emit("input", "topsecret");
			expect(wrapper.emitted("input")).toStrictEqual([["topsecret"]]);
		});
	});

	// slots
	describe("options slot", () => {
		it("passes the options to the k-field", () => {
			const wrapper = field({}, { options: "<button>Options</button>" });
			expect(wrapper.find(".options button").text()).toBe("Options");
		});

		it("passes no empty options slot to the k-field", () => {
			const wrapper = field();
			expect(wrapper.find(".options").exists()).toBe(false);
		});
	});
});
