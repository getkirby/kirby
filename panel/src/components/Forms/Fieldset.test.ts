import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import type { App } from "vue";
import Fieldset from "./Fieldset.vue";

const TextField = {
	name: "k-text-field",
	props: ["disabled", "formData", "label", "name", "value"],
	emits: ["focus", "input", "submit"],
	methods: { focus: vi.fn() },
	template: "<input />"
};

const fields = {
	title: { label: "Title", type: "text" },
	subtitle: { label: "Subtitle", type: "text", width: "1/2" }
};

function mount(props = {}, attrs = {}) {
	return vueMount(Fieldset, {
		props: { fields, value: { subtitle: "B", title: "A" }, ...props },
		attrs,
		global: {
			components: { "k-text-field": TextField }
		}
	});
}

function inputs(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(TextField);
}

describe("k-fieldset", () => {
	// lets $helper.isComponent() know about the field components
	beforeEach(() => {
		const app = { _context: { components: { "k-text-field": TextField } } };
		vi.stubGlobal("panel", { ...window.panel, app: app as unknown as App });
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-fieldset");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders the fields in a grid", () => {
			const wrapper = mount();
			expect(wrapper.find("k-grid").attributes("variant")).toBe("fields");
		});
	});

	// props
	describe("disabled prop", () => {
		it("disables all fields", () => {
			const wrapper = mount({ disabled: true });
			expect(
				inputs(wrapper).map((input) => input.props("disabled"))
			).toStrictEqual([true, true]);
		});

		it("disables a single field", () => {
			const wrapper = mount({
				fields: { ...fields, title: { ...fields.title, disabled: true } }
			});
			const disabled = inputs(wrapper).map((input) => input.props("disabled"));
			expect(disabled.map((state) => state === true)).toStrictEqual([
				true,
				false
			]);
		});
	});

	describe("fields prop", () => {
		it("renders a field component for each field", () => {
			const wrapper = mount();
			expect(inputs(wrapper).map((input) => input.props("name"))).toStrictEqual(
				["title", "subtitle"]
			);
			expect(inputs(wrapper)[0].props("label")).toBe("Title");
		});

		it("sets the width of the column", () => {
			const wrapper = mount();
			const columns = wrapper.findAll("k-column");
			expect(columns[1].attributes("width")).toBe("1/2");
		});

		it("renders an error for an unknown field type", () => {
			const wrapper = mount({ fields: { map: { type: "map" } } });
			expect(wrapper.find("k-box").attributes("theme")).toBe("negative");
			expect(wrapper.find("k-box").text()).toBe("error.field.type.missing");
		});

		it("hides fields by their conditions", () => {
			const wrapper = mount({
				fields: {
					...fields,
					subtitle: { ...fields.subtitle, when: { title: "B" } }
				}
			});
			expect(inputs(wrapper)).toHaveLength(1);
		});
	});

	describe("value prop", () => {
		it("passes the values to the fields", () => {
			const wrapper = mount();
			const [title] = inputs(wrapper);
			expect(title.props("value")).toBe("A");
			expect(title.props("formData")).toStrictEqual({
				subtitle: "B",
				title: "A"
			});
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the field with the name", () => {
			const wrapper = mount();
			const [, subtitle] = inputs(wrapper);
			const focus = vi.spyOn(subtitle.vm, "focus");
			wrapper.vm.focus("subtitle");
			expect(focus).toHaveBeenCalledOnce();
		});

		it("focuses the first field without name", () => {
			const wrapper = mount();
			const [title] = inputs(wrapper);
			const focus = vi.spyOn(title.vm, "focus");
			wrapper.vm.focus();
			expect(focus).toHaveBeenCalledOnce();
		});

		it("ignores an unknown field", () => {
			const wrapper = mount();
			expect(() => wrapper.vm.focus("unknown")).not.toThrow();
		});
	});

	describe("hasField()", () => {
		it("finds a rendered field", () => {
			const wrapper = mount();
			expect(wrapper.vm.hasField("title")).toBeTruthy();
			expect(wrapper.vm.hasField("unknown")).toBeFalsy();
		});
	});

	describe("hasFieldType()", () => {
		it("knows registered field types", () => {
			const wrapper = mount();
			expect(wrapper.vm.hasFieldType("text")).toBe(true);
			expect(wrapper.vm.hasFieldType("map")).toBe(false);
		});
	});

	describe("onInput()", () => {
		it("emits the values with the new value", () => {
			const wrapper = mount();
			wrapper.vm.onInput("C", fields.title, "title");
			expect(wrapper.emitted("input")).toStrictEqual([
				[{ subtitle: "B", title: "C" }, fields.title, "title"]
			]);
		});
	});

	// events
	describe("focus event", () => {
		it("emits the focus of a field", () => {
			const wrapper = mount();
			const event = new FocusEvent("focus");
			inputs(wrapper)[0].vm.$emit("focus", event);
			expect(wrapper.emitted("focus")).toStrictEqual([
				[event, fields.title, "title"]
			]);
		});
	});

	describe("input event", () => {
		it("emits the values on input in a field", () => {
			const wrapper = mount();
			inputs(wrapper)[1].vm.$emit("input", "C");
			expect(wrapper.emitted("input")).toStrictEqual([
				[{ subtitle: "C", title: "A" }, fields.subtitle, "subtitle"]
			]);
		});
	});

	describe("submit event", () => {
		it("emits the submit of a field", () => {
			const wrapper = mount();
			inputs(wrapper)[0].vm.$emit("submit", "A");
			expect(wrapper.emitted("submit")).toStrictEqual([
				["A", fields.title, "title"]
			]);
		});
	});
});
