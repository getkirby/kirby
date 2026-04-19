import { describe, it, expect, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import Button from "./Button.vue";

describe("Button.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Button, "BUTTON", "k-button");
		it.acceptsClass(Button);
		it.acceptsStyle(Button);
		it.inheritsNoAttrs(Button);
	});

	// computed
	describe("component computed", () => {
		it("renders as a button by default", () => {
			const wrapper = mount(Button);
			expect(wrapper.element.tagName).toBe("BUTTON");
		});

		it("renders as k-link when link prop is set", () => {
			const wrapper = mount(Button, { props: { link: "/path" } });
			expect(wrapper.element.tagName).toBe("K-LINK");
		});

		it("renders as the specified element", () => {
			const wrapper = mount(Button, { props: { element: "a" } });
			expect(wrapper.element.tagName).toBe("A");
		});

		it("element prop takes precedence over link", () => {
			const wrapper = mount(Button, {
				props: { element: "span", link: "/path" }
			});
			expect(wrapper.element.tagName).toBe("SPAN");
		});
	});

	// props
	describe("badge prop", () => {
		it("renders badge text in k-button-badge span", () => {
			const wrapper = mount(Button, {
				props: { badge: { text: "5", theme: "positive" } }
			});
			expect(wrapper.find(".k-button-badge").text()).toBe("5");
		});

		it("uses badge theme as data-theme", () => {
			const wrapper = mount(Button, {
				props: { badge: { text: "5", theme: "positive" }, theme: "negative" }
			});
			expect(wrapper.find(".k-button-badge").attributes("data-theme")).toBe(
				"positive"
			);
		});

		it("falls back to button theme when badge has no theme", () => {
			const wrapper = mount(Button, {
				props: { badge: { text: "5" }, theme: "negative" }
			});
			expect(wrapper.find(".k-button-badge").attributes("data-theme")).toBe(
				"negative"
			);
		});
	});

	describe("current prop", () => {
		it("renders as aria-current attribute", () => {
			const wrapper = mount(Button, { props: { current: true } });
			expect(wrapper.attributes("aria-current")).toBe("true");
		});
	});

	describe("disabled prop", () => {
		it("renders as aria-disabled attribute", () => {
			const wrapper = mount(Button, { props: { disabled: true } });
			expect(wrapper.attributes("aria-disabled")).toBe("true");
		});
	});

	describe("dropdown prop", () => {
		it("renders k-button-arrow when text is set", () => {
			const wrapper = mount(Button, {
				props: { dropdown: true, text: "Menu" }
			});
			expect(wrapper.find(".k-button-arrow").exists()).toBe(true);
		});

		it("does not render k-button-arrow without text", () => {
			const wrapper = mount(Button, { props: { dropdown: true } });
			expect(wrapper.find(".k-button-arrow").exists()).toBe(false);
		});
	});

	describe("icon prop", () => {
		it("sets data-has-icon and renders k-button-icon when set", () => {
			const wrapper = mount(Button, { props: { icon: "edit" } });
			expect(wrapper.attributes("data-has-icon")).toBe("true");
			expect(wrapper.find(".k-button-icon k-icon").attributes("type")).toBe(
				"edit"
			);
		});

		it("omits data-has-icon and hides k-button-icon when not set", () => {
			const wrapper = mount(Button);
			expect(wrapper.attributes("data-has-icon")).toBe("false");
			expect(wrapper.find(".k-button-icon").exists()).toBe(false);
		});
	});

	describe("selected prop", () => {
		it("renders as aria-selected attribute", () => {
			const wrapper = mount(Button, { props: { selected: true } });
			expect(wrapper.attributes("aria-selected")).toBe("true");
		});
	});

	describe("size prop", () => {
		it("renders as data-size attribute", () => {
			const wrapper = mount(Button, { props: { size: "sm" } });
			expect(wrapper.attributes("data-size")).toBe("sm");
		});
	});

	describe("text prop", () => {
		it("sets data-has-text and renders k-button-text when set", () => {
			const wrapper = mount(Button, { props: { text: "Save" } });
			expect(wrapper.attributes("data-has-text")).toBe("true");
			expect(wrapper.find(".k-button-text").text()).toBe("Save");
		});

		it("omits data-has-text and hides k-button-text when not set", () => {
			const wrapper = mount(Button);
			expect(wrapper.attributes("data-has-text")).toBe("false");
			expect(wrapper.find(".k-button-text").exists()).toBe(false);
		});
	});

	describe("theme prop", () => {
		it("renders as data-theme attribute", () => {
			const wrapper = mount(Button, { props: { theme: "positive" } });
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});
	});

	describe("type prop", () => {
		it("defaults to button", () => {
			const wrapper = mount(Button);
			expect(wrapper.attributes("type")).toBe("button");
		});

		it("reflects the prop", () => {
			const wrapper = mount(Button, { props: { type: "submit" } });
			expect(wrapper.attributes("type")).toBe("submit");
		});
	});

	describe("variant prop", () => {
		it("renders as data-variant attribute", () => {
			const wrapper = mount(Button, { props: { variant: "filled" } });
			expect(wrapper.attributes("data-variant")).toBe("filled");
		});
	});

	// slots
	describe("default slot", () => {
		it("sets data-has-text to true", () => {
			const wrapper = mount(Button, { slots: { default: "Save" } });
			expect(wrapper.attributes("data-has-text")).toBe("true");
		});

		it("renders slotted content in k-button-text", () => {
			const slot = "<span>Custom</span>";
			const wrapper = mount(Button, { slots: { default: slot } });
			expect(wrapper.find(".k-button-text").element.innerHTML).toContain(slot);
		});
	});

	// events
	describe("click event", () => {
		it("emits click when clicked", async () => {
			const wrapper = mount(Button);
			await wrapper.trigger("click");
			expect(wrapper.emitted("click")).toHaveLength(1);
		});

		it("calls click prop when clicked", async () => {
			const onClick = vi.fn();
			const wrapper = mount(Button, { props: { click: onClick } });
			await wrapper.trigger("click");
			expect(onClick).toHaveBeenCalled();
		});

		it("does not emit click when disabled", async () => {
			const wrapper = mount(Button, { props: { disabled: true } });
			await wrapper.trigger("click");
			expect(wrapper.emitted("click")).toBeUndefined();
		});

		it("opens dialog when dialog prop is set", async () => {
			const open = vi.fn();
			const wrapper = mount(Button, {
				props: { dialog: "my-dialog" },
				global: { mocks: { $panel: { dialog: { open } } } }
			});
			await wrapper.trigger("click");
			expect(open).toHaveBeenCalledWith("my-dialog");
		});

		it("opens drawer when drawer prop is set", async () => {
			const open = vi.fn();
			const wrapper = mount(Button, {
				props: { drawer: "my-drawer" },
				global: { mocks: { $panel: { drawer: { open } } } }
			});
			await wrapper.trigger("click");
			expect(open).toHaveBeenCalledWith("my-drawer");
		});
	});
});
