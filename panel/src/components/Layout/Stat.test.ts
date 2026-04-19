import { describe, it, expect, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import Stat from "./Stat.vue";

describe("Stat.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(Stat, "DIV", "k-stat");
		it.acceptsClass(Stat);
		it.acceptsStyle(Stat);
	});

	// props
	describe("click prop", () => {
		it("renders as k-link", () => {
			const wrapper = mount(Stat, { props: { click: () => {} } });
			expect(wrapper.find("k-link").exists()).toBe(true);
		});
	});

	describe("dialog prop", () => {
		it("renders as k-link", () => {
			const wrapper = mount(Stat, { props: { dialog: "pages/create" } });
			expect(wrapper.find("k-link").exists()).toBe(true);
		});
	});

	describe("drawer prop", () => {
		it("renders as k-link", () => {
			const wrapper = mount(Stat, { props: { drawer: "pages/preview" } });
			expect(wrapper.find("k-link").exists()).toBe(true);
		});
	});

	describe("icon prop", () => {
		it("renders a k-icon with the correct type", () => {
			const wrapper = mount(Stat, { props: { label: "Pages", icon: "page" } });
			expect(wrapper.find("k-icon").attributes("type")).toBe("page");
		});

		it("does not render a k-icon when not provided", () => {
			const wrapper = mount(Stat, { props: { label: "Pages" } });
			expect(wrapper.find("k-icon").exists()).toBe(false);
		});
	});

	describe("info prop", () => {
		it("renders info text", () => {
			const wrapper = mount(Stat, { props: { info: "Last week" } });
			expect(wrapper.find(".k-stat-info").text()).toBe("Last week");
		});

		it("does not render info element when not provided", () => {
			const wrapper = mount(Stat);
			expect(wrapper.find(".k-stat-info").exists()).toBe(false);
		});
	});

	describe("label prop", () => {
		it("renders label text", () => {
			const wrapper = mount(Stat, { props: { label: "Pages" } });
			expect(wrapper.find(".k-stat-label").text()).toBe("Pages");
		});

		it("does not render label element when not provided", () => {
			const wrapper = mount(Stat);
			expect(wrapper.find(".k-stat-label").exists()).toBe(false);
		});
	});

	describe("link prop", () => {
		it("renders as k-link with the correct to attribute", () => {
			const wrapper = mount(Stat, { props: { link: "/pages" } });
			expect(wrapper.find("k-link").attributes("to")).toBe("/pages");
		});
	});

	describe("theme prop", () => {
		it("renders value as attribute", () => {
			const wrapper = mount(Stat, { props: { theme: "positive" } });
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});
	});

	describe("value prop", () => {
		it("renders value text", () => {
			const wrapper = mount(Stat, { props: { value: "123" } });
			expect(wrapper.find(".k-stat-value").text()).toBe("123");
		});

		it("does not render value element when not provided", () => {
			const wrapper = mount(Stat);
			expect(wrapper.find(".k-stat-value").exists()).toBe(false);
		});
	});

	// computed
	describe("component computed", () => {
		it("renders as div when target is null", () => {
			const wrapper = mount(Stat);
			expect(wrapper.element.tagName).toBe("DIV");
		});

		it("renders as k-link when target is not null", () => {
			const wrapper = mount(Stat, { props: { link: "/pages" } });
			expect(wrapper.find("k-link").attributes("to")).toBe("/pages");
		});
	});

	describe("target computed", () => {
		it("returns null by default", () => {
			const wrapper = mount(Stat);
			expect((wrapper.vm as any).target).toBeNull();
		});

		it("returns the link", () => {
			const wrapper = mount(Stat, { props: { link: "/pages" } });
			expect((wrapper.vm as any).target).toBe("/pages");
		});

		it("returns the click function", () => {
			const click = () => {};
			const wrapper = mount(Stat, { props: { click } });
			expect((wrapper.vm as any).target).toBe(click);
		});

		it("returns a function that opens the dialog", () => {
			const open = vi.fn();
			const wrapper = mount(Stat, {
				props: { dialog: "pages/create" },
				global: { mocks: { $panel: { dialog: { open } } } }
			});
			(wrapper.vm as any).target();
			expect(open).toHaveBeenCalledWith("pages/create");
		});

		it("returns a function that opens the drawer", () => {
			const open = vi.fn();
			const wrapper = mount(Stat, {
				props: { drawer: "pages/preview" },
				global: { mocks: { $panel: { drawer: { open } } } }
			});
			(wrapper.vm as any).target();
			expect(open).toHaveBeenCalledWith("pages/preview");
		});

	});
});
