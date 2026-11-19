import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import FlagFieldPreview from "./FlagFieldPreview.vue";

const Button = {
	name: "k-button",
	props: ["dialog", "disabled", "icon", "size", "theme", "title"],
	template: "<button />"
};

// the status button that the page list builds for each page
const flag = {
	dialog: "pages/a/changeStatus",
	disabled: false,
	icon: "status-draft",
	size: "xs",
	theme: "negative-icon",
	title: "Status: Draft"
};

function mount(props = {}, attrs = {}) {
	return vueMount(FlagFieldPreview, {
		props: { value: flag, ...props },
		attrs,
		global: {
			stubs: { "k-button": Button }
		}
	});
}

describe("k-flag-field-preview", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "BUTTON", "k-flag-field-preview");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("value prop", () => {
		it("renders the status button in medium size", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(Button).props()).toStrictEqual({
				...flag,
				size: "md"
			});
		});

		it("fills in the button for a page status", () => {
			const wrapper = mount({ value: { status: "listed" } });
			expect(wrapper.findComponent(Button).props()).toMatchObject({
				disabled: false,
				icon: "status-listed",
				size: "md",
				theme: "positive-icon"
			});
		});

		it("renders nothing without value", () => {
			const wrapper = mount({ value: null });
			expect(wrapper.findComponent(Button).exists()).toBe(false);
		});
	});
});
