import { describe, expect, it } from "@test/unit";
import { mount } from "@vue/test-utils";
import ModelForm from "./ModelForm.vue";

const { disabled, isEmpty, resolvedColumns } = ModelForm.computed!;
const { fieldsWithAdditionalData } = ModelForm.methods!;

type Field = Record<string, unknown>;
type Fields = Record<string, Field>;
type ResolvedField = Field & {
	endpoints: Record<string, string>;
	hasDiff: boolean;
};
type ResolvedFields = Record<string, ResolvedField>;
type Column = { fields?: Fields; sticky?: boolean; width?: string };
type ResolvedColumn = Column & { fields: ResolvedFields };

interface Context {
	api?: string;
	columns?: Record<string, Column>;
	diff?: Record<string, unknown>;
	fieldsWithAdditionalData: (fields: Fields) => ResolvedFields;
}

/**
 * Builds a mocked component context
 */
function context(props: Partial<Context> = {}): Context {
	const ctx = {
		api: "pages/test",
		columns: {},
		diff: {},
		...props
	} as Context;

	ctx.fieldsWithAdditionalData = fieldsWithAdditionalData.bind(
		ctx
	) as Context["fieldsWithAdditionalData"];

	return ctx;
}

describe("ModelForm.vue", () => {
	const columns = {
		0: { width: "1/2", fields: { headline: { type: "text" } } },
		1: { width: "1/2", sticky: true, fields: { text: { type: "textarea" } } }
	};

	it("renders a column for each column of the tab", () => {
		const wrapper = mount(ModelForm, {
			props: { api: "pages/test", columns, content: { headline: "Test" } }
		});

		const rendered = wrapper.findAll("k-column");

		expect(wrapper.find("form.k-model-form").exists()).toBe(true);
		expect(rendered.length).toBe(2);
		expect(rendered[0].attributes("width")).toBe("1/2");
		expect(rendered[1].attributes("sticky")).toBe("true");
		expect(wrapper.findAll("k-fieldset").length).toBe(2);
	});

	it("renders the empty state instead of the form", () => {
		const wrapper = mount(ModelForm, {
			props: { columns: {}, empty: "No blueprint" }
		});

		expect(wrapper.find("k-box").exists()).toBe(true);
		expect(wrapper.find("form").exists()).toBe(false);
	});

	it("passes on the input of a fieldset", async () => {
		const wrapper = mount(ModelForm, {
			props: { api: "pages/test", columns }
		});

		await wrapper.find("k-fieldset").trigger("input");

		expect(wrapper.emitted("input")).toHaveLength(1);
	});

	it("submits the form and the fieldsets", async () => {
		const wrapper = mount(ModelForm, {
			props: { api: "pages/test", columns }
		});

		await wrapper.find("form").trigger("submit");
		expect(wrapper.emitted("submit")).toHaveLength(1);

		// the submit of a fieldset bubbles up to the form as well
		await wrapper.find("k-fieldset").trigger("submit");
		expect(wrapper.emitted("submit")).toHaveLength(3);
	});
});

describe("ModelForm.fieldsWithAdditionalData()", () => {
	it("points regular fields at the field endpoint", () => {
		const ctx = context();
		const fields = ctx.fieldsWithAdditionalData({
			headline: { type: "text" }
		});

		expect(fields.headline.endpoints).toStrictEqual({
			model: "pages/test",
			field: "pages/test/fields/headline"
		});
	});

	it("points section fields at the section endpoint", () => {
		const ctx = context();
		const fields = ctx.fieldsWithAdditionalData({
			mysection: { type: "section" }
		});

		expect(fields.mysection.endpoints).toStrictEqual({
			model: "pages/test",
			section: "pages/test/sections/mysection"
		});
	});

	it("flags fields with unsaved changes", () => {
		const ctx = context({ diff: { headline: "changed" } });
		const fields = ctx.fieldsWithAdditionalData({
			headline: { type: "text" },
			text: { type: "textarea" }
		});

		expect(fields.headline.hasDiff).toBe(true);
		expect(fields.text.hasDiff).toBe(false);
	});

	it("survives a missing diff", () => {
		const ctx = context({ diff: undefined });
		const fields = ctx.fieldsWithAdditionalData({
			headline: { type: "text" }
		});

		expect(fields.headline.hasDiff).toBe(false);
	});
});

describe("ModelForm.resolvedColumns()", () => {
	it("keeps the column props and resolves its fields", () => {
		const ctx = context({
			columns: {
				0: { width: "2/3", fields: { headline: { type: "text" } } }
			}
		});

		const columns = resolvedColumns.call(ctx) as Record<string, ResolvedColumn>;

		expect(columns[0].width).toBe("2/3");
		expect(columns[0].fields.headline.endpoints.field).toBe(
			"pages/test/fields/headline"
		);
	});
});

describe("ModelForm.disabled()", () => {
	it("is disabled while the model is locked", () => {
		expect(disabled.call({ lock: { state: "lock" } })).toBe(true);
		expect(disabled.call({ lock: { state: "unlock" } })).toBe(false);
		expect(disabled.call({ lock: false })).toBe(false);
	});
});

describe("ModelForm.isEmpty()", () => {
	it("only reports empty when there are no columns and a text to show", () => {
		expect(isEmpty.call({ columns: {}, empty: "No blueprint" })).toBe(
			"No blueprint"
		);
		expect(isEmpty.call({ columns: {}, empty: null })).toBeFalsy();
		expect(isEmpty.call({ columns: { 0: {} }, empty: "No blueprint" })).toBe(
			false
		);
	});
});
