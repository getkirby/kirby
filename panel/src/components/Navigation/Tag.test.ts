import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import html from "@/panel/html";
import Tag from "./Tag.vue";

function mount(props = {}, attrs = {}, slots = {}) {
	return vueMount(Tag, { props, attrs, slots, attachTo: document.body });
}

describe("k-tag", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "BUTTON", "k-tag");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("is a plain button", () => {
			const wrapper = mount();
			expect(wrapper.attributes("type")).toBe("button");
		});
	});

	// props
	describe("disabled prop", () => {
		it("marks the tag as disabled", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.attributes("aria-disabled")).toBe("true");
		});

		it("hides the remove button", () => {
			const wrapper = mount({ disabled: true, removable: true });
			expect(wrapper.find(".k-tag-toggle").exists()).toBe(false);
		});
	});

	describe("element prop", () => {
		it("renders the tag as the element", () => {
			const wrapper = mount({ element: "li" });
			expect(wrapper.element.tagName).toBe("LI");
		});
	});

	describe("html prop", () => {
		it("does not warn when the deprecated prop is unused", () => {
			mount({ text: "Design" });
			expect(window.panel.deprecated).not.toHaveBeenCalled();
		});

		it("renders the text as trusted HTML", () => {
			const wrapper = mount({ html: true, text: "<b>Design</b>" });
			expect(wrapper.find(".k-tag-text b").text()).toBe("Design");
		});

		it("warns that the prop is deprecated", () => {
			mount({ html: true, text: "Design" });
			expect(window.panel.deprecated).toHaveBeenCalledWith(
				expect.stringContaining("`html` prop has been deprecated")
			);
		});
	});

	describe("icon prop", () => {
		it("renders an icon frame", () => {
			const wrapper = mount({ icon: "heart" });
			const frame = wrapper.find("k-icon-frame.k-tag-image");
			expect(frame.attributes("icon")).toBe("heart");
			expect(frame.attributes("ratio")).toBe("1/1");
		});

		it("renders no frame by default", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-tag-image").exists()).toBe(false);
		});
	});

	describe("image prop", () => {
		it("renders an image frame for an image", () => {
			const wrapper = mount({ image: { src: "/a.jpg" } });
			const frame = wrapper.find("k-image-frame.k-tag-image");
			expect(frame.attributes("src")).toBe("/a.jpg");
			expect(frame.attributes("ratio")).toBe("1/1");
		});

		it("renders an icon frame without src", () => {
			const wrapper = mount({ image: { back: "pattern", icon: "page" } });
			const frame = wrapper.find("k-icon-frame.k-tag-image");
			expect(frame.attributes("icon")).toBe("page");
			expect(frame.attributes("back")).toBe("pattern");
		});
	});

	describe("link prop", () => {
		it("renders the tag as k-link", () => {
			const wrapper = mount({ link: "/pages/a" });
			expect(wrapper.element.tagName).toBe("K-LINK");
			expect(wrapper.attributes("to")).toBe("/pages/a");
		});
	});

	describe("removable prop", () => {
		it("renders a remove button", () => {
			const wrapper = mount({ removable: true });
			const toggle = wrapper.find(".k-tag-toggle");
			expect(toggle.attributes("icon")).toBe("cancel-small");
		});

		it("makes the tag focusable", () => {
			const wrapper = mount({ element: "div", removable: true });
			expect(wrapper.attributes("tabindex")).toBe("0");
		});

		it("renders no remove button by default", () => {
			const wrapper = mount();
			expect(wrapper.find(".k-tag-toggle").exists()).toBe(false);
			expect(wrapper.attributes("tabindex")).toBeUndefined();
		});
	});

	describe("text prop", () => {
		it("escapes a plain string", () => {
			const wrapper = mount({ text: "<b>Design</b>" });
			const text = wrapper.find(".k-tag-text");
			expect(text.find("b").exists()).toBe(false);
			expect(text.text()).toBe("<b>Design</b>");
		});

		it("renders trusted HTML as is", () => {
			const wrapper = mount({ text: html("<b>Design</b>") });
			expect(wrapper.find(".k-tag-text b").text()).toBe("Design");
		});
	});

	describe("theme prop", () => {
		it("defaults to dark", () => {
			const wrapper = mount();
			expect(wrapper.attributes("data-theme")).toBe("dark");
		});

		it("sets the theme", () => {
			const wrapper = mount({ theme: "positive" });
			expect(wrapper.attributes("data-theme")).toBe("positive");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the tag", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(document.activeElement).toBe(wrapper.element);
		});
	});

	describe("remove()", () => {
		it("emits remove for a removable tag", () => {
			const wrapper = mount({ removable: true });
			wrapper.vm.remove();
			expect(wrapper.emitted("remove")).toHaveLength(1);
		});

		it("emits nothing for a tag that is not removable", () => {
			const wrapper = mount();
			wrapper.vm.remove();
			expect(wrapper.emitted("remove")).toBeUndefined();
		});

		it("emits nothing for a disabled tag", () => {
			const wrapper = mount({ disabled: true, removable: true });
			wrapper.vm.remove();
			expect(wrapper.emitted("remove")).toBeUndefined();
		});
	});

	// events
	describe("remove event", () => {
		it("emits on a click on the remove button without clicking the tag", async () => {
			const wrapper = mount({ removable: true });
			let clicked = false;
			wrapper.element.addEventListener("click", () => (clicked = true));
			await wrapper.find(".k-tag-toggle").trigger("click");
			expect(wrapper.emitted("remove")).toHaveLength(1);
			expect(clicked).toBe(false);
		});

		it("emits on the delete key", async () => {
			const wrapper = mount({ removable: true });
			await wrapper.trigger("keydown.delete");
			expect(wrapper.emitted("remove")).toHaveLength(1);
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the slot as text", () => {
			const wrapper = mount({}, {}, { default: "Design" });
			expect(wrapper.find(".k-tag-text").text()).toBe("Design");
		});

		it("prefers the text prop", () => {
			const wrapper = mount({ text: "Text" }, {}, { default: "Slot" });
			expect(wrapper.find(".k-tag-text").text()).toBe("Text");
		});
	});

	describe("image slot", () => {
		it("replaces the frame", () => {
			const wrapper = mount(
				{ icon: "heart" },
				{},
				{ image: "<span class='custom' />" }
			);
			expect(wrapper.find("k-icon-frame").exists()).toBe(false);
			expect(wrapper.find(".custom").exists()).toBe(true);
		});
	});
});
