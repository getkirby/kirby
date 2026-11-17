import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import Field from "./Field.vue";

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Field, { props, attrs, slots });
}

describe("k-field", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("counter prop", () => {
		it("renders no k-counter by default", () => {
			const wrapper = mount({ label: "Title" });
			expect(wrapper.find("k-counter").exists()).toBe(false);
		});

		it("renders a k-counter with the counter options", () => {
			const wrapper = mount({ counter: { count: 3, max: 10 }, label: "Title" });
			const counter = wrapper.find(".k-field-header k-counter");
			expect(counter.attributes("count")).toBe("3");
			expect(counter.attributes("max")).toBe("10");
			expect(counter.classes()).toContain("k-field-counter");
		});
	});

	describe("disabled prop", () => {
		it("sets data-disabled", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("data-disabled")).toBe("true");
		});

		it("is not disabled by default", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-disabled")).toBe("false");
		});
	});

	describe("hasDiff prop", () => {
		it("passes the diff state to the k-label", () => {
			const wrapper = mount({ hasDiff: true, label: "Title" });
			expect(wrapper.find("k-label").attributes("has-diff")).toBe("true");
		});
	});

	describe("help prop", () => {
		it("renders the help text in the footer", () => {
			const wrapper = mount({ help: "Some help" });
			const help = wrapper.find(".k-field-footer k-text");
			expect(help.attributes("text")).toBe("Some help");
			expect(help.classes()).toStrictEqual(["k-help", "k-field-help"]);
		});

		it("renders no footer without help", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-field-footer").exists()).toBe(false);
		});
	});

	describe("input prop", () => {
		it("links the k-label to the input", () => {
			const wrapper = mount({ input: "title-input", label: "Title" });
			expect(wrapper.find("k-label").attributes("input")).toBe("title-input");
		});
	});

	describe("label prop", () => {
		it("renders a k-label in the header", () => {
			const wrapper = mount({ label: "Title" });
			const label = wrapper.find(".k-field-header k-label");
			expect(label.text()).toBe("Title");
			expect(label.attributes("title")).toBe("Title");
			expect(label.attributes("type")).toBe("field");
		});

		it("renders no header without label", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-field-header").exists()).toBe(false);
		});
	});

	describe("link prop", () => {
		it("passes the link to the k-label", () => {
			const wrapper = mount({ label: "Title", link: "/pages/a" });
			expect(wrapper.find("k-label").attributes("link")).toBe("/pages/a");
		});
	});

	describe("name prop", () => {
		it("adds the name as class", () => {
			const wrapper = mount({ name: "title" });
			expect(wrapper.classes()).toContain("k-field-name-title");
		});

		it("adds no name class without name", () => {
			const wrapper = mount();
			expect(wrapper.classes()).toStrictEqual(["k-field"]);
		});
	});

	describe("required prop", () => {
		it("passes the required state to the k-label", () => {
			const wrapper = mount({ label: "Title", required: true });
			expect(wrapper.find("k-label").attributes("required")).toBe("true");
		});

		it("passes the required state to the k-counter", () => {
			const wrapper = mount({
				counter: { count: 3, required: false },
				label: "Title",
				required: true
			});
			expect(wrapper.find("k-counter").attributes("required")).toBe("true");
		});
	});

	describe("translate prop", () => {
		it("sets data-translate", () => {
			const wrapper = mount({ translate: true });
			expect(wrapper.attributes("data-translate")).toBe("true");
		});
	});

	describe("type prop", () => {
		it("adds the type as class", () => {
			const wrapper = mount({ type: "text" });
			expect(wrapper.classes()).toContain("k-field-type-text");
		});

		it("adds no type class without type", () => {
			const wrapper = mount({ name: "title" });
			expect(wrapper.classes()).toStrictEqual(["k-field", "k-field-name-title"]);
		});
	});

	// events
	describe("blur event", () => {
		it("emits when the focus leaves an element inside", async () => {
			const wrapper = mount({}, {}, { default: "<input />" });
			await wrapper.find("input").trigger("focusout");
			expect(wrapper.emitted("blur")).toHaveLength(1);
		});
	});

	describe("focus event", () => {
		it("emits when an element inside receives focus", async () => {
			const wrapper = mount({}, {}, { default: "<input />" });
			await wrapper.find("input").trigger("focusin");
			expect(wrapper.emitted("focus")).toHaveLength(1);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders slotted content between header and footer", () => {
			const wrapper = mount(
				{ help: "Some help", label: "Title" },
				{},
				{ default: "<input />" }
			);
			const tags = wrapper
				.findAll(":scope > *")
				.map((el) => el.element.tagName);
			expect(tags).toStrictEqual(["HEADER", "INPUT", "FOOTER"]);
		});
	});

	describe("counter slot", () => {
		it("replaces the k-counter", () => {
			const wrapper = mount(
				{ counter: { count: 3 }, label: "Title" },
				{},
				{ counter: "<span class='custom' />" }
			);
			expect(wrapper.find("k-counter").exists()).toBe(false);
			expect(wrapper.find(".k-field-header .custom").exists()).toBe(true);
		});

		it("renders the header without label", () => {
			const wrapper = mount({}, {}, { counter: "<span class='custom' />" });
			expect(wrapper.find(".k-field-header .custom").exists()).toBe(true);
		});
	});

	describe("footer slot", () => {
		it("replaces the footer", () => {
			const wrapper = mount(
				{ help: "Some help" },
				{},
				{ footer: "<p class='custom' />" }
			);
			expect(wrapper.find(".k-field-footer").exists()).toBe(false);
			expect(wrapper.find(".custom").exists()).toBe(true);
		});
	});

	describe("header slot", () => {
		it("replaces the header", () => {
			const wrapper = mount(
				{ label: "Title" },
				{},
				{ header: "<p class='custom' />" }
			);
			expect(wrapper.find(".k-field-header").exists()).toBe(false);
			expect(wrapper.find(".custom").exists()).toBe(true);
		});
	});

	describe("help slot", () => {
		it("replaces the help text", () => {
			const wrapper = mount(
				{ help: "Some help" },
				{},
				{ help: "<p class='custom' />" }
			);
			expect(wrapper.find("k-text").exists()).toBe(false);
			expect(wrapper.find(".k-field-footer .custom").exists()).toBe(true);
		});

		it("renders the footer without help", () => {
			const wrapper = mount({}, {}, { help: "<p class='custom' />" });
			expect(wrapper.find(".k-field-footer .custom").exists()).toBe(true);
		});
	});

	describe("label slot", () => {
		it("replaces the k-label", () => {
			const wrapper = mount(
				{ label: "Title" },
				{},
				{ label: "<span class='custom' />" }
			);
			expect(wrapper.find("k-label").exists()).toBe(false);
			expect(wrapper.find(".k-field-header .custom").exists()).toBe(true);
		});

		it("renders the header without label", () => {
			const wrapper = mount({}, {}, { label: "<span class='custom' />" });
			expect(wrapper.find(".k-field-header .custom").exists()).toBe(true);
		});
	});

	describe("options slot", () => {
		it("renders the options after the k-label", () => {
			const wrapper = mount(
				{ label: "Title" },
				{},
				{ options: "<span class='custom' />" }
			);
			const tags = wrapper
				.findAll(".k-field-header > *")
				.map((el) => el.element.tagName);
			expect(tags).toStrictEqual(["K-LABEL", "SPAN"]);
		});

		it("renders the header without label", () => {
			const wrapper = mount({}, {}, { options: "<span class='custom' />" });
			expect(wrapper.find(".k-field-header .custom").exists()).toBe(true);
		});
	});
});
