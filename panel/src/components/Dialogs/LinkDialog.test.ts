import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import LinkDialog from "./LinkDialog.vue";

const FormDialog = {
	name: "k-form-dialog",
	inheritAttrs: false,
	props: { fields: [Array, Object] },
	template: "<div />"
};

function mount(props = {}) {
	return vueMount(LinkDialog, {
		props,
		global: { stubs: { "k-form-dialog": FormDialog } }
	});
}

describe("LinkDialog.vue", () => {
	// computed
	describe("dialog computed", () => {
		it("passes the language to the title field", () => {
			const wrapper = mount({ dir: "rtl", lang: "ar" });
			const fields = wrapper.findComponent(FormDialog).props("fields");

			expect(fields.title.dir).toBe("rtl");
			expect(fields.title.lang).toBe("ar");
			expect(fields.href.dir).toBeUndefined();
			expect(fields.href.lang).toBeUndefined();
		});

		it("does not pass dir and lang to the form dialog", () => {
			const wrapper = mount({ dir: "rtl", lang: "ar" });
			const attrs = wrapper.findComponent(FormDialog).vm.$attrs;

			expect(attrs).not.toHaveProperty("dir");
			expect(attrs).not.toHaveProperty("lang");
		});

		it("keeps fields without a title", () => {
			const fields = { href: { type: "text" } };
			const wrapper = mount({ dir: "rtl", fields });

			expect(wrapper.findComponent(FormDialog).props("fields")).toEqual(fields);
		});
	});
});
