import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TimeField from "./TimeField.vue";

const Dropdown = {
	name: "k-dropdown",
	methods: { close: vi.fn(), toggle: vi.fn() },
	template: "<div><slot /></div>"
};
const Input = {
	name: "k-input",
	props: ["display", "id", "step", "type", "value"],
	emits: ["input", "submit"],
	methods: { focus: vi.fn() },
	template: `<div><slot name="icon" /></div>`
};
const Times = {
	name: "k-timeoptions-input",
	props: ["display", "value"],
	emits: ["input"],
	template: "<div />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(TimeField, {
		props,
		attrs,
		global: {
			stubs: {
				"k-dropdown": Dropdown,
				"k-input": Input,
				"k-timeoptions-input": Times
			}
		}
	});
}

describe("k-time-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-time-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("renders a time k-input", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Input).props("type")).toBe("time");
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables the button for the time options", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.find("k-button").attributes("disabled")).toBe("true");
		});
	});

	describe("display prop", () => {
		it("passes the display format to the input and time options", () => {
			const wrapper = mount({ display: "HH:mm" });
			expect(wrapper.findComponent(Input).props("display")).toBe("HH:mm");
			expect(wrapper.findComponent(Times).props("display")).toBe("HH:mm");
		});
	});

	describe("icon prop", () => {
		it("defaults to clock", () => {
			const wrapper = mount();
			expect(wrapper.find("k-button").attributes("icon")).toBe("clock");
		});

		it("sets the icon of the button", () => {
			const wrapper = mount({ icon: "alarm" });
			expect(wrapper.find("k-button").attributes("icon")).toBe("alarm");
		});
	});

	describe("id prop", () => {
		it("links the k-field label to the k-input", () => {
			const wrapper = mount({ id: "time" });
			expect(wrapper.attributes("input")).toBe("time");
			expect(wrapper.findComponent(Input).props("id")).toBe("time");
		});
	});

	describe("times prop", () => {
		it("offers time options by default", () => {
			const wrapper = mount({ value: "10:00:00" });
			expect(wrapper.findComponent(Times).props("value")).toBe("10:00:00");
		});

		it("offers no time options when turned off", () => {
			const wrapper = mount({ times: false });
			expect(wrapper.findComponent(Times).exists()).toBe(false);
			expect(wrapper.find("k-button").exists()).toBe(false);
		});
	});

	describe("value prop", () => {
		it("passes the value to the k-input", () => {
			const wrapper = mount({ value: "10:00:00" });
			expect(wrapper.findComponent(Input).props("value")).toBe("10:00:00");
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
		it("emits the time and closes the time options", () => {
			const wrapper = mount();
			wrapper.vm.select("09:30:00");
			expect(wrapper.emitted("input")).toStrictEqual([["09:30:00"]]);
			expect(Dropdown.methods.close).toHaveBeenCalledOnce();
		});
	});

	// events
	describe("click event", () => {
		it("toggles the time options", async () => {
			const wrapper = mount();
			await wrapper.find("k-button").trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it("emits the value of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("input", "10:00:00");
			expect(wrapper.emitted("input")).toStrictEqual([["10:00:00"]]);
		});

		it("emits an empty string for a cleared k-input", () => {
			const wrapper = mount({ value: "10:00:00" });
			wrapper.findComponent(Input).vm.$emit("input", null);
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});

		it("emits the time picked in the time options", () => {
			const wrapper = mount();
			wrapper.findComponent(Times).vm.$emit("input", "09:30:00");
			expect(wrapper.emitted("input")).toStrictEqual([["09:30:00"]]);
		});
	});

	describe("submit event", () => {
		it("emits the submit of the k-input", () => {
			const wrapper = mount();
			wrapper.findComponent(Input).vm.$emit("submit");
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});
	});
});
