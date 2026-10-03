import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ModelForm from "./ModelForm.vue";

type Field = Record<string, unknown>;
type Fields = Record<string, Field>;
type ResolvedField = Field & {
	endpoints: Record<string, string>;
	hasDiff: boolean;
};
type ResolvedFields = Record<string, ResolvedField>;
type Column = { fields?: Fields; sticky?: boolean; width?: string };
type ResolvedColumn = Column & { fields: ResolvedFields };

const columns = {
	0: { width: "1/2", fields: { headline: { type: "text" } } },
	1: { width: "1/2", sticky: true, fields: { text: { type: "textarea" } } }
};

function mount(props = {}, attrs = {}) {
	return vueMount(ModelForm, {
		props: { api: "pages/test", columns, ...props },
		attrs
	});
}

describe("ModelForm.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "FORM", "k-model-form");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
	describe("columns prop", () => {
		it("renders a column for each column of the tab", () => {
			const wrapper = mount({ content: { headline: "Test" } });
			const rendered = wrapper.findAll("k-column");

			expect(rendered.length).toBe(2);
			expect(rendered[0].attributes("width")).toBe("1/2");
			expect(rendered[1].attributes("sticky")).toBe("true");
			expect(wrapper.findAll("k-fieldset").length).toBe(2);
		});
	});

	describe("empty prop", () => {
		it("renders the empty state instead of the form", () => {
			const wrapper = mount({ columns: {}, empty: "No blueprint" });

			expect(wrapper.find("k-box").exists()).toBe(true);
			expect(wrapper.find("form").exists()).toBe(false);
		});
	});

	describe("lock prop", () => {
		it("disables the fieldsets of a locked model", () => {
			const wrapper = mount({
				lock: { isLegacy: false, isLocked: true, modified: null, user: {} }
			});

			expect(wrapper.attributes("data-locked")).toBe("true");

			for (const fieldset of wrapper.findAll("k-fieldset")) {
				expect(fieldset.attributes("disabled")).toBe("true");
			}
		});

		it("keeps the fieldsets of an unlocked model editable", () => {
			const wrapper = mount({
				lock: { isLegacy: false, isLocked: false, modified: null, user: {} }
			});

			expect(wrapper.attributes("data-locked")).toBe("false");

			for (const fieldset of wrapper.findAll("k-fieldset")) {
				expect(fieldset.attributes("disabled")).toBe("false");
			}
		});
	});

	// computed
	describe("isEmpty computed", () => {
		it("only reports empty when there are no columns and a text to show", () => {
			expect(mount({ columns: {}, empty: "No blueprint" }).vm.isEmpty).toBe(
				"No blueprint"
			);
			expect(mount({ columns: {} }).vm.isEmpty).toBeFalsy();
			expect(mount({ empty: "No blueprint" }).vm.isEmpty).toBe(false);
		});
	});

	describe("isLocked computed", () => {
		it("reads the lock state from the lock payload", () => {
			expect(mount({ lock: { isLocked: true } }).vm.isLocked).toBe(true);
			expect(mount({ lock: { isLocked: false } }).vm.isLocked).toBe(false);
			expect(mount({ lock: false }).vm.isLocked).toBe(false);
			expect(mount().vm.isLocked).toBe(false);
		});
	});

	describe("resolvedColumns computed", () => {
		it("keeps the column props and resolves its fields", () => {
			const wrapper = mount({
				columns: {
					0: { width: "2/3", fields: { headline: { type: "text" } } }
				}
			});

			const resolved = wrapper.vm.resolvedColumns as Record<
				string,
				ResolvedColumn
			>;

			expect(resolved[0].width).toBe("2/3");
			expect(resolved[0].fields.headline.endpoints.field).toBe(
				"pages/test/fields/headline"
			);
		});
	});

	// methods
	describe("fieldsWithAdditionalData()", () => {
		function resolve(props = {}, fields: Fields = {}): ResolvedFields {
			return mount(props).vm.fieldsWithAdditionalData(fields) as ResolvedFields;
		}

		it("points regular fields at the field endpoint", () => {
			const fields = resolve({}, { headline: { type: "text" } });

			expect(fields.headline.endpoints).toStrictEqual({
				model: "pages/test",
				field: "pages/test/fields/headline"
			});
		});

		it("flags fields with unsaved changes", () => {
			const fields = resolve(
				{ diff: { headline: "changed" } },
				{ headline: { type: "text" }, text: { type: "textarea" } }
			);

			expect(fields.headline.hasDiff).toBe(true);
			expect(fields.text.hasDiff).toBe(false);
		});

		it("survives a missing diff", () => {
			const fields = resolve(
				{ diff: undefined },
				{ headline: { type: "text" } }
			);
			expect(fields.headline.hasDiff).toBe(false);
		});
	});

	// events
	describe("input event", () => {
		it("passes on the input of a fieldset", async () => {
			const wrapper = mount();
			await wrapper.find("k-fieldset").trigger("input");
			expect(wrapper.emitted("input")).toHaveLength(1);
		});
	});

	describe("submit event", () => {
		it("submits the form and the fieldsets", async () => {
			const wrapper = mount();

			await wrapper.find("form").trigger("submit");
			expect(wrapper.emitted("submit")).toHaveLength(1);

			// the submit of a fieldset bubbles up to the form as well
			await wrapper.find("k-fieldset").trigger("submit");
			expect(wrapper.emitted("submit")).toHaveLength(3);
		});
	});
});
