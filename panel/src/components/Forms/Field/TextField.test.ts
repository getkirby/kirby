import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type { App } from "vue";
import TextField from "./TextField.vue";

const Field = {
	name: "k-field",
	props: ["counter", "input", "label", "type"],
	template: `<div>
		<div v-if="$slots.options" class="options"><slot name="options" /></div>
		<slot />
	</div>`
};
const Input = {
	name: "k-input",
	props: ["id", "placeholder", "type", "value"],
	emits: ["input"],
	methods: { focus: vi.fn(), select: vi.fn() },
	template: "<input />"
};

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(TextField, {
		props,
		attrs,
		slots,
		global: {
			stubs: { "k-input": Input }
		}
	});
}

// a literal <k-field> tag only renders the default slot
function field(props = {}, slots = {}) {
	return vueMount(TextField, {
		props,
		slots,
		global: {
			stubs: { "k-field": Field, "k-input": Input }
		}
	});
}

describe("k-text-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-text-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("counter prop", () => {
		it("passes the counter options to the k-field", () => {
			const wrapper = field({ maxlength: 10, value: "abc" });
			expect(wrapper.findComponent(Field).props("counter")).toStrictEqual({
				count: 3,
				max: 10,
				min: undefined
			});
		});

		it("passes no counter when hidden", () => {
			const wrapper = field({ counter: false, value: "abc" });
			expect(wrapper.findComponent(Field).props("counter")).toBe(false);
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = field({ id: "title" });
			expect(wrapper.findComponent(Field).props("input")).toBe("title");
			expect(wrapper.findComponent(Input).props("id")).toBe("title");
		});
	});

	describe("label prop", () => {
		it("passes the label to the k-field", () => {
			const wrapper = field({ label: "Title" });
			expect(wrapper.findComponent(Field).props("label")).toBe("Title");
		});
	});

	describe("placeholder prop", () => {
		it("passes the placeholder to the k-input", () => {
			const wrapper = mount({ placeholder: "Title …" });
			expect(wrapper.findComponent(Input).props("placeholder")).toBe("Title …");
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "abc" });
			expect(wrapper.findComponent(Input).props("value")).toBe("abc");
		});
	});

	// computed
	describe("inputType computed", () => {
		it("uses the text input by default", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("text");
		});

		it("uses the input of a registered type", () => {
			const app = { _context: { components: { "k-email-input": {} } } };
			vi.stubGlobal("panel", { ...window.panel, app: app as unknown as App });
			const wrapper = mount({ type: "email" });
			expect(wrapper.findComponent(Input).props("type")).toBe("email");
		});

		it("falls back to the text input for an unknown type", () => {
			const wrapper = mount({ type: "unknown" });
			expect(wrapper.findComponent(Input).props("type")).toBe("text");
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

	describe("select()", () => {
		it("selects the value of the k-input", () => {
			const wrapper = mount();
			wrapper.vm.select();
			expect(Input.methods.select).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("input event", () => {
		it("emits the value of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "abc");
			expect(wrapper.emitted("input")).toStrictEqual([["abc"]]);
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
