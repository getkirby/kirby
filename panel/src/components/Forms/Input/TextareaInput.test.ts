import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import TextareaInput from "./TextareaInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(TextareaInput, {
		props: { buttons: false, ...props },
		attrs,
		global: {
			mocks: {
				$panel: { dialog: { open: vi.fn() } }
			}
		}
	});
}

describe("TextareaInput.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "DIV", "k-textarea-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("dir prop", () => {
		it("sets the reading direction of the textarea only", () => {
			const wrapper = mount({ dir: "rtl" });
			expect(wrapper.attributes("dir")).toBeUndefined();
			expect(wrapper.find("textarea").attributes("dir")).toBe("rtl");
		});

		it("inherits the reading direction without dir", () => {
			const wrapper = mount();
			expect(wrapper.find("textarea").attributes("dir")).toBeUndefined();
		});
	});

	describe("lang prop", () => {
		it("sets the language of the textarea only", () => {
			const wrapper = mount({ lang: "de" });
			expect(wrapper.attributes("lang")).toBeUndefined();
			expect(wrapper.find("textarea").attributes("lang")).toBe("de");
		});

		it("inherits the language without lang", () => {
			const wrapper = mount();
			expect(wrapper.find("textarea").attributes("lang")).toBeUndefined();
		});
	});

	// methods
	describe("dialog()", () => {
		it("passes the language to the toolbar dialog", () => {
			const wrapper = mount({ dir: "rtl", lang: "ar" });
			wrapper.vm.dialog("link");

			expect(wrapper.vm.$panel.dialog.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-toolbar-link-dialog",
					props: expect.objectContaining({ dir: "rtl", lang: "ar" })
				})
			);
		});
	});
});
