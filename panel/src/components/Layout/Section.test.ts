import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Section from "./Section.vue";

const ButtonGroup = {
	name: "k-button-group",
	props: ["buttons", "size", "variant"],
	template: "<nav />"
};
const Label = {
	name: "k-label",
	props: ["invalid", "link", "required", "title", "type"],
	template: "<h2><slot /></h2>"
};

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Section, {
		props: { label: "Pages", ...props },
		attrs,
		slots: { default: "<p>Content</p>", ...slots },
		global: {
			stubs: { "k-button-group": ButtonGroup, "k-label": Label }
		}
	});
}

function label(wrapper: ReturnType<typeof mount>) {
	return wrapper.findComponent(Label);
}

describe("k-section", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "SECTION", "k-section");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a header with a section label", () => {
			const wrapper = mount();
			expect(wrapper.find("header.k-section-header").exists()).toBe(true);
			expect(label(wrapper).text()).toBe("Pages");
			expect(label(wrapper).props()).toStrictEqual({
				invalid: false,
				link: undefined,
				required: false,
				title: "Pages",
				type: "section"
			});
		});

		it("has no header without label, headline, buttons or options", () => {
			const wrapper = mount({ label: undefined });
			expect(wrapper.find("header").exists()).toBe(false);
		});
	});

	// props
	describe("buttons prop", () => {
		const buttons = [{ icon: "add", text: "Add" }];

		it("renders the buttons", () => {
			const wrapper = mount({ buttons });
			const group = wrapper.findComponent(ButtonGroup);
			expect(group.classes()).toContain("k-section-buttons");
			expect(group.props()).toStrictEqual({
				buttons,
				size: "xs",
				variant: "filled"
			});
		});

		it("renders a header for the buttons alone", () => {
			const wrapper = mount({ buttons, label: undefined });
			expect(wrapper.find("header").exists()).toBe(true);
		});

		it("renders no buttons by default", () => {
			expect(mount().findComponent(ButtonGroup).exists()).toBe(false);
		});
	});

	describe("headline prop", () => {
		it("is the label without label", () => {
			const wrapper = mount({ headline: "Drafts", label: undefined });
			expect(label(wrapper).text()).toBe("Drafts");
			expect(label(wrapper).props("title")).toBe("Drafts");
		});

		it("gives way to the label", () => {
			const wrapper = mount({ headline: "Drafts" });
			expect(label(wrapper).text()).toBe("Pages");
		});
	});

	describe("invalid prop", () => {
		it("marks the section and label as invalid", () => {
			const wrapper = mount({ invalid: true });
			expect(wrapper.attributes("data-invalid")).toBe("true");
			expect(label(wrapper).props("invalid")).toBe(true);
		});
	});

	describe("link prop", () => {
		it("links the label", () => {
			const wrapper = mount({ link: "/pages/blog" });
			expect(label(wrapper).props("link")).toBe("/pages/blog");
		});
	});

	describe("required prop", () => {
		it("marks the label as required", () => {
			const wrapper = mount({ required: true });
			expect(label(wrapper).props("required")).toBe(true);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the content after the header", () => {
			const wrapper = mount();
			expect(wrapper.element.lastElementChild?.outerHTML).toBe(
				"<p>Content</p>"
			);
		});
	});

	describe("options slot", () => {
		it("replaces the buttons", () => {
			const wrapper = mount(
				{ buttons: [{ text: "Add" }] },
				{},
				{ options: `<button class="custom">Sort</button>` }
			);
			expect(wrapper.find("header .custom").text()).toBe("Sort");
			expect(wrapper.findComponent(ButtonGroup).exists()).toBe(false);
		});

		it("renders a header for the options alone", () => {
			const wrapper = mount(
				{ label: undefined },
				{},
				{ options: `<button class="custom">Sort</button>` }
			);
			expect(wrapper.find("header .custom").exists()).toBe(true);
		});
	});
});
