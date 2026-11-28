import { afterAll, beforeAll, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import DateField from "./DateField.vue";

const Calendar = {
	name: "k-calendar-input",
	props: ["max", "min", "value"],
	emits: ["input"],
	template: "<div />"
};
const Dropdown = {
	name: "k-dropdown",
	methods: { close: vi.fn(), toggle: vi.fn() },
	template: "<div><slot /></div>"
};
const Input = {
	name: "k-input",
	props: [
		"disabled",
		"display",
		"icon",
		"max",
		"min",
		"required",
		"step",
		"type",
		"value"
	],
	emits: ["input", "submit"],
	methods: { focus: vi.fn() },
	template: `<div :class="'input-' + type"><slot name="icon" /></div>`
};
const Times = {
	name: "k-timeoptions-input",
	props: ["display", "value"],
	emits: ["input"],
	template: "<div />"
};

function mount(props = {}, attrs = {}) {
	return vueMount(DateField, {
		props,
		attrs,
		global: {
			stubs: {
				"k-calendar-input": Calendar,
				"k-dropdown": Dropdown,
				"k-input": Input,
				"k-timeoptions-input": Times
			}
		}
	});
}

function inputs(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Input);
}

describe("k-date-field", () => {
	beforeAll(() => {
		vi.useFakeTimers();
		vi.setSystemTime(new Date(2022, 0, 15, 14, 30));
	});

	afterAll(() => {
		vi.useRealTimers();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-date-field");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	// props
	describe("calendar prop", () => {
		it("offers a calendar by default", () => {
			const wrapper = mount({ max: "2022-12-31", min: "2022-01-01" });
			const calendar = wrapper.findComponent(Calendar);
			expect(calendar.props("max")).toBe("2022-12-31");
			expect(calendar.props("min")).toBe("2022-01-01");
			expect(wrapper.find(".input-date k-button").attributes("title")).toBe(
				"date.select"
			);
		});

		it("offers no calendar when turned off", () => {
			const wrapper = mount({ calendar: false });
			expect(wrapper.findComponent(Calendar).exists()).toBe(false);
		});

		it("offers no calendar when disabled", () => {
			const wrapper = mount({ disabled: true });
			expect(wrapper.findComponent(Calendar).exists()).toBe(false);
		});
	});

	describe("icon prop", () => {
		it("defaults to calendar", () => {
			const wrapper = mount();
			expect(wrapper.find(".input-date k-button").attributes("icon")).toBe(
				"calendar"
			);
		});
	});

	describe("time prop", () => {
		it("adds a time input by default", () => {
			const wrapper = mount();
			expect(
				wrapper.find(".k-date-field-body").attributes("data-has-time")
			).toBe("true");
			expect(inputs(wrapper).map((input) => input.props("type"))).toStrictEqual(
				["date", "time"]
			);
		});

		it("passes the time options to the time input", () => {
			const step = { size: 30, unit: "minute" };
			const wrapper = mount({
				time: { display: "HH:mm", icon: "alarm", step }
			});
			const time = inputs(wrapper)[1];
			expect(time.props("display")).toBe("HH:mm");
			expect(time.props("icon")).toBe("alarm");
			expect(time.props("step")).toStrictEqual(step);
		});

		it("adds no time input when turned off", () => {
			const wrapper = mount({ time: false });
			expect(inputs(wrapper)).toHaveLength(1);
			expect(
				wrapper.find(".k-date-field-body").attributes("data-has-time")
			).toBe("false");
		});

		it("offers time options by default", () => {
			const wrapper = mount({ time: { display: "HH:mm" } });
			expect(wrapper.findComponent(Times).props("display")).toBe("HH:mm");
		});

		it("offers no time options when turned off", () => {
			const wrapper = mount({ time: { times: false } });
			expect(wrapper.findComponent(Times).exists()).toBe(false);
		});
	});

	describe("min & max props", () => {
		it("pass the time to the time input on the boundary day", () => {
			const wrapper = mount({
				max: "2024-09-19 18:00:00",
				min: "2024-09-19 14:16:10",
				value: "2024-09-19 15:00:00"
			});
			const time = inputs(wrapper)[1];
			expect(time.props("min")).toBe("14:16:10");
			expect(time.props("max")).toBe("18:00:00");
		});

		it("pass no time to the time input on another day", () => {
			const wrapper = mount({
				max: "2024-09-30 18:00:00",
				min: "2024-09-19 14:16:10",
				value: "2024-09-20 15:00:00"
			});
			const time = inputs(wrapper)[1];
			expect(time.props("min")).toBeUndefined();
			expect(time.props("max")).toBeUndefined();
		});

		it("pass the time once the date moves onto the boundary day", async () => {
			const wrapper = mount({
				min: "2024-09-19 14:16:10",
				value: "2024-09-20 15:00:00"
			});
			inputs(wrapper)[0].vm.$emit("input", "2024-09-19");
			await wrapper.vm.$nextTick();
			expect(inputs(wrapper)[1].props("min")).toBe("14:16:10");
		});
	});

	describe("value prop", () => {
		it("passes the date and time parts to the inputs", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			expect(inputs(wrapper)[0].props("value")).toBe("2022-03-04");
			expect(inputs(wrapper)[1].props("value")).toBe("05:06:07");
		});
	});

	// computed
	describe("isEmpty computed", () => {
		it("is empty without date and time", () => {
			const wrapper = mount();
			expect(wrapper.vm.isEmpty).toBe(true);
		});

		it("is empty without time when it uses time", () => {
			const wrapper = mount({ value: "2022-03-04" });
			wrapper.vm.iso.time = null;
			expect(wrapper.vm.isEmpty).toBe(true);
		});

		it("needs only a date without time", () => {
			const wrapper = mount({ time: false, value: "2022-03-04" });
			expect(wrapper.vm.isEmpty).toBe(false);
		});
	});

	// watch
	describe("value watcher", () => {
		it("passes the new parts to the inputs", async () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			await wrapper.setProps({ value: "2023-04-05 06:07:08" } as never);
			expect(inputs(wrapper)[0].props("value")).toBe("2023-04-05");
			expect(inputs(wrapper)[1].props("value")).toBe("06:07:08");
		});
	});

	// methods
	describe("focus()", () => {
		it("focuses the date input", () => {
			const wrapper = mount();
			wrapper.vm.focus();
			expect(Input.methods.focus).toHaveBeenCalledOnce();
		});
	});

	describe("now()", () => {
		it("returns the current date and time", () => {
			const wrapper = mount();
			expect(wrapper.vm.now()).toStrictEqual({
				date: "2022-01-15",
				time: "14:30:00"
			});
		});

		it("uses midnight without time", () => {
			const wrapper = mount({ time: false });
			expect(wrapper.vm.now()).toMatchObject({ time: "00:00:00" });
		});
	});

	describe("onDateInput()", () => {
		it("emits the date with the current time for an empty time", () => {
			const wrapper = mount();
			wrapper.vm.onDateInput("2022-03-04");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-03-04 14:30:00"]]);
		});

		it("keeps the time", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			wrapper.vm.onDateInput("2022-03-05");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-03-05 05:06:07"]]);
		});

		it("emits an empty string when cleared", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			wrapper.vm.onDateInput("");
			expect(wrapper.emitted("input")).toStrictEqual([[""]]);
		});
	});

	describe("onTimeInput()", () => {
		it("emits the time with today for an empty date", () => {
			const wrapper = mount();
			wrapper.vm.onTimeInput("08:00:00");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-01-15 08:00:00"]]);
		});

		it("keeps the date", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			wrapper.vm.onTimeInput("08:00:00");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-03-04 08:00:00"]]);
		});
	});

	describe("onTimesInput()", () => {
		it("closes the time options and emits the time", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			wrapper.vm.onTimesInput("09:30:00");
			expect(Dropdown.methods.close).toHaveBeenCalledOnce();
			expect(wrapper.emitted("input")).toStrictEqual([["2022-03-04 09:30:00"]]);
		});
	});

	describe("timeBoundary()", () => {
		it("returns the time of a boundary on the same day", () => {
			const wrapper = mount({ value: "2024-09-19 15:00:00" });
			expect(wrapper.vm.timeBoundary("2024-09-19 14:16:10")).toBe("14:16:10");
		});

		it("returns nothing for a boundary on another day", () => {
			const wrapper = mount({ value: "2024-09-20 15:00:00" });
			expect(wrapper.vm.timeBoundary("2024-09-19 14:16:10")).toBeUndefined();
		});

		it("returns nothing without a date", () => {
			const wrapper = mount();
			expect(wrapper.vm.timeBoundary("2024-09-19 14:16:10")).toBeUndefined();
		});

		it("returns nothing without a boundary", () => {
			const wrapper = mount({ value: "2024-09-19 15:00:00" });
			expect(wrapper.vm.timeBoundary(undefined)).toBeUndefined();
		});
	});

	describe("toIso()", () => {
		it("splits a value into date and time", () => {
			const wrapper = mount();
			expect(wrapper.vm.toIso("2022-03-04 05:06:07")).toStrictEqual({
				date: "2022-03-04",
				time: "05:06:07"
			});
		});

		it("returns null parts for no date", () => {
			const wrapper = mount();
			expect(wrapper.vm.toIso("")).toStrictEqual({ date: null, time: null });
		});
	});

	// events
	describe("click event", () => {
		it("toggles the calendar", async () => {
			const wrapper = mount();
			await wrapper.find(".input-date k-button").trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});

		it("toggles the time options", async () => {
			const wrapper = mount();
			await wrapper.find(".input-time k-button").trigger("click");
			expect(Dropdown.methods.toggle).toHaveBeenCalledOnce();
		});
	});

	describe("input event", () => {
		it("emits the date picked in the calendar", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			wrapper.findComponent(Calendar).vm.$emit("input", "2022-03-10");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-03-10 05:06:07"]]);
		});

		it("emits the time picked in the time options", () => {
			const wrapper = mount({ value: "2022-03-04 05:06:07" });
			wrapper.findComponent(Times).vm.$emit("input", "10:00:00");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-03-04 10:00:00"]]);
		});
	});

	describe("submit event", () => {
		it.each([[0], [1]])("emits the submit of input %i", (index) => {
			const wrapper = mount();
			inputs(wrapper)[index].vm.$emit("submit");
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});
	});
});
