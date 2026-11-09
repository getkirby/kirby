import { describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type Editor from "@/components/Forms/Writer/Editor";
import WriterInput from "./WriterInput.vue";

function mount(props = {}, attrs = {}) {
	return vueMount(WriterInput, {
		props,
		attrs,
		attachTo: document.body,
		global: {
			mocks: {
				$panel: {
					dialog: { open: vi.fn() },
					events: { on: vi.fn(), off: vi.fn() }
				}
			}
		}
	});
}

describe("WriterInput.vue", () => {
	// $el
	describe("element", () => {
		const component = (attrs = {}) => mount({}, attrs);

		it.rendersAs(component, "DIV", "k-writer-input");
		it.acceptsClass(component);
		it.acceptsStyle(component);
		it.inheritsNoAttrs(component);
	});

	// props
	describe("dir prop", () => {
		it("sets the reading direction of the editor and its toolbar", () => {
			const wrapper = mount({ dir: "rtl" });
			expect(wrapper.attributes("dir")).toBe("rtl");
		});

		it("inherits the reading direction without dir", () => {
			const wrapper = mount();
			expect(wrapper.attributes("dir")).toBeUndefined();
		});
	});

	describe("lang prop", () => {
		it("sets the language of the editable element only", () => {
			const wrapper = mount({ lang: "de" });
			expect(wrapper.attributes("lang")).toBeUndefined();
			expect(wrapper.find(".ProseMirror").attributes("lang")).toBe("de");
		});

		it("inherits the language without lang", () => {
			const wrapper = mount();
			expect(wrapper.find(".ProseMirror").attributes("lang")).toBeUndefined();
		});
	});

	// watch
	describe("lang watcher", () => {
		it("updates the language of the editable element", async () => {
			const wrapper = mount({ lang: "de" });
			await wrapper.setProps({ lang: "fr" } as object);
			expect(wrapper.find(".ProseMirror").attributes("lang")).toBe("fr");

			await wrapper.setProps({ lang: undefined } as object);
			expect(wrapper.find(".ProseMirror").attributes("lang")).toBeUndefined();
		});
	});

	// events
	describe("editor email event", () => {
		it("passes the language to the email dialog", () => {
			const wrapper = mount({ dir: "rtl", lang: "ar" });
			const editor = wrapper.vm.editor as unknown as Editor;
			editor.emit("email", editor);

			expect(wrapper.vm.$panel.dialog.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-email-dialog",
					props: expect.objectContaining({ dir: "rtl", lang: "ar" })
				})
			);
		});
	});

	describe("editor link event", () => {
		it("passes the language to the link dialog", () => {
			const wrapper = mount({ dir: "rtl", lang: "ar" });
			const editor = wrapper.vm.editor as unknown as Editor;
			editor.emit("link", editor);

			expect(wrapper.vm.$panel.dialog.open).toHaveBeenCalledWith(
				expect.objectContaining({
					component: "k-link-dialog",
					props: expect.objectContaining({ dir: "rtl", lang: "ar" })
				})
			);
		});
	});
});
