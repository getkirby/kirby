import { afterAll, beforeAll, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import CalendarInput from "./CalendarInput.vue";

const Select = {
	name: "k-select-input",
	props: ["ariaLabel", "autofocus", "options", "required", "value"],
	emits: ["input"],
	template: "<select />"
};

function mount(props = {}, attrs = {}, weekday = 1) {
	return vueMount(CalendarInput, {
		props,
		attrs,
		global: {
			mocks: { $panel: { translation: { weekday } } },
			stubs: { "k-select-input": Select }
		}
	});
}

function day(wrapper: ReturnType<typeof mount>, text: string) {
	return wrapper.find(`.k-calendar-day k-button[text="${text}"]`);
}

function selects(wrapper: ReturnType<typeof mount>) {
	return wrapper.findAllComponents(Select);
}

function hidden(wrapper: ReturnType<typeof mount>) {
	return wrapper.find<HTMLInputElement>("input.input-hidden");
}

describe("k-calendar-input", () => {
	// a Saturday, just like the first day of the month
	beforeAll(() => {
		vi.useFakeTimers();
		vi.setSystemTime(new Date(2022, 0, 15));
	});

	afterAll(() => {
		vi.useRealTimers();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "FIELDSET", "k-calendar-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);

		it("has a legend for screen readers", () => {
			const wrapper = mount();
			const legend = wrapper.find("legend");
			expect(legend.classes()).toContain("sr-only");
			expect(legend.text()).toBe("date.select");
		});

		it("has a hidden date input for the form", () => {
			const wrapper = mount();
			expect(hidden(wrapper).attributes("type")).toBe("date");
			expect(hidden(wrapper).attributes("tabindex")).toBe("-1");
		});
	});

	// props
	describe("autofocus prop", () => {
		it("focuses the month select", () => {
			const wrapper = mount({ autofocus: true });
			expect(selects(wrapper)[0].props("autofocus")).toBe(true);
		});
	});

	describe("disabled prop", () => {
		it("disables the days, today and the hidden input", () => {
			const wrapper = mount({ disabled: true });
			expect(day(wrapper, "1").attributes("disabled")).toBe("true");
			expect(
				wrapper.find(".k-calendar-today k-button").attributes("disabled")
			).toBe("true");
			expect(hidden(wrapper).element.disabled).toBe(true);
		});
	});

	describe.each([
		["id", "date"],
		["max", "2022-12-31"],
		["min", "2022-01-01"],
		["name", "date"]
	])("%s prop", (prop, value) => {
		it("sets the attribute on the hidden input", () => {
			const wrapper = mount({ [prop]: value });
			expect(hidden(wrapper).attributes(prop)).toBe(value);
		});
	});

	describe("max prop", () => {
		it("disables the days after the maximum", () => {
			const wrapper = mount({ max: "2022-01-20" });
			expect(day(wrapper, "20").attributes("disabled")).toBe("false");
			expect(day(wrapper, "21").attributes("disabled")).toBe("true");
		});
	});

	describe("min prop", () => {
		it("disables the days before the minimum", () => {
			const wrapper = mount({ min: "2022-01-10" });
			expect(day(wrapper, "9").attributes("disabled")).toBe("true");
			expect(day(wrapper, "10").attributes("disabled")).toBe("false");
		});
	});

	describe("required prop", () => {
		it("requires the hidden input", () => {
			const wrapper = mount({ required: true });
			expect(hidden(wrapper).element.required).toBe(true);
		});
	});

	describe("value prop", () => {
		it("shows and selects the day of the value", () => {
			const wrapper = mount({ value: "2023-03-07" });
			expect(selects(wrapper)[0].props("value")).toBe(2);
			expect(selects(wrapper)[1].props("value")).toBe(2023);
			expect(
				day(wrapper, "7").element.parentElement?.getAttribute("aria-selected")
			).toBe("date");
			expect(hidden(wrapper).element.value).toBe("2023-03-07");
		});

		it("shows the current month without value", () => {
			const wrapper = mount();
			expect(selects(wrapper)[0].props("value")).toBe(0);
			expect(selects(wrapper)[1].props("value")).toBe(2022);
		});
	});

	// computed
	describe("firstWeekday computed", () => {
		it("offsets the first day for weeks starting on Monday", () => {
			const wrapper = mount();
			expect(wrapper.vm.firstWeekday).toBe(5);
		});

		it("offsets the first day for weeks starting on Sunday", () => {
			const wrapper = mount({}, {}, 0);
			expect(wrapper.vm.firstWeekday).toBe(6);
		});
	});

	describe("months computed", () => {
		it("offers all months", () => {
			const wrapper = mount();
			const options = selects(wrapper)[0].props("options");
			expect(options).toHaveLength(12);
			expect(options[0]).toStrictEqual({ text: "January", value: 0 });
			expect(options[11]).toStrictEqual({ text: "December", value: 11 });
		});
	});

	describe("numberOfDays computed", () => {
		it("counts the days of the shown month", async () => {
			const wrapper = mount();
			expect(wrapper.vm.numberOfDays).toBe(31);
			wrapper.vm.onNext();
			expect(wrapper.vm.numberOfDays).toBe(28);
		});
	});

	describe("weekdays computed", () => {
		it("starts the week on the weekday of the translation", () => {
			const wrapper = mount();
			const days = wrapper.findAll("th").map((th) => th.text());
			expect(days).toStrictEqual([
				"Mon",
				"Tue",
				"Wed",
				"Thu",
				"Fri",
				"Sat",
				"Sun"
			]);
		});

		it("starts the week on Sunday", () => {
			const wrapper = mount({}, {}, 0);
			expect(wrapper.find("th").text()).toBe("Sun");
		});
	});

	describe("weeks computed", () => {
		it("renders a row for each week", () => {
			const wrapper = mount();
			expect(wrapper.findAll("tbody tr")).toHaveLength(6);
		});
	});

	describe("years computed", () => {
		it("offers 20 years around the shown year", () => {
			const wrapper = mount();
			const options = selects(wrapper)[1].props("options");
			expect(options).toHaveLength(41);
			expect(options[0]).toStrictEqual({ text: "2002", value: 2002 });
			expect(options[40]).toStrictEqual({ text: "2042", value: 2042 });
		});
	});

	// watch
	describe("value watcher", () => {
		it("shows and selects the new value", async () => {
			const wrapper = mount({ value: "2022-01-20" });
			await wrapper.setProps({ value: "2022-05-03" } as never);
			expect(selects(wrapper)[0].props("value")).toBe(4);
			expect(wrapper.vm.isSelected(3)).toBe(true);
		});
	});

	// methods
	describe("days()", () => {
		it("fills the first week with placeholders", () => {
			const wrapper = mount();
			expect(wrapper.vm.days(1)).toStrictEqual(["", "", "", "", "", 1, 2]);
		});

		it("fills the last week with placeholders", () => {
			const wrapper = mount();
			expect(wrapper.vm.days(6)).toStrictEqual([31, "", "", "", "", "", ""]);
		});
	});

	describe("isToday()", () => {
		it("marks today", () => {
			const wrapper = mount();
			expect(wrapper.vm.isToday(15)).toBe(true);
			expect(
				day(wrapper, "15").element.parentElement?.getAttribute("aria-current")
			).toBe("date");
		});
	});

	describe("onNext()", () => {
		it("shows the next month and year", () => {
			const wrapper = mount({ value: "2022-12-24" });
			wrapper.vm.onNext();
			expect(wrapper.vm.month).toBe(0);
			expect(wrapper.vm.year).toBe(2023);
		});
	});

	describe("onPrev()", () => {
		it("shows the previous month and year", () => {
			const wrapper = mount();
			wrapper.vm.onPrev();
			expect(wrapper.vm.month).toBe(11);
			expect(wrapper.vm.year).toBe(2021);
		});
	});

	describe("select()", () => {
		it("emits the ISO date", () => {
			const wrapper = mount();
			wrapper.vm.select(wrapper.vm.toDate(3));
			expect(wrapper.emitted("input")).toStrictEqual([["2022-01-03"]]);
		});

		it("emits null without date", () => {
			const wrapper = mount();
			wrapper.vm.select(null);
			expect(wrapper.emitted("input")).toStrictEqual([[null]]);
		});
	});

	describe("show()", () => {
		it("shows the month of today without date", () => {
			const wrapper = mount({ value: "2023-03-07" });
			wrapper.vm.show(null);
			expect(wrapper.vm.month).toBe(0);
			expect(wrapper.vm.year).toBe(2022);
		});
	});

	describe("toDate()", () => {
		it("creates a date in the shown month", () => {
			const wrapper = mount();
			expect(wrapper.vm.toDate(3).toISO("date")).toBe("2022-01-03");
			expect(wrapper.vm.toDate(3, 5).toISO("date")).toBe("2022-06-03");
		});
	});

	describe("toOptions()", () => {
		it("creates padded options between min and max", () => {
			const wrapper = mount();
			expect(wrapper.vm.toOptions(8, 10)).toStrictEqual([
				{ text: "08", value: 8 },
				{ text: "09", value: 9 },
				{ text: "10", value: 10 }
			]);
		});
	});

	// events
	describe("click event", () => {
		it("emits the clicked day", async () => {
			const wrapper = mount();
			await day(wrapper, "3").trigger("click");
			expect(wrapper.emitted("input")).toStrictEqual([["2022-01-03"]]);
		});

		it("shows and emits today", async () => {
			const wrapper = mount({ value: "2023-03-07" });
			await wrapper.find(".k-calendar-today k-button").trigger("click");
			expect(wrapper.vm.month).toBe(0);
			expect(wrapper.emitted("input")).toStrictEqual([["2022-01-15"]]);
		});

		it("shows the previous and next month", async () => {
			const wrapper = mount();
			await wrapper.find('nav k-button[icon="angle-right"]').trigger("click");
			expect(wrapper.vm.month).toBe(1);
			await wrapper.find('nav k-button[icon="angle-left"]').trigger("click");
			expect(wrapper.vm.month).toBe(0);
		});

		it("keeps clicks inside the calendar", async () => {
			const wrapper = mount();
			const parent = document.createElement("div");
			let bubbled = false;
			parent.addEventListener("click", () => (bubbled = true));
			parent.append(wrapper.element);
			await day(wrapper, "3").trigger("click");
			expect(bubbled).toBe(false);
		});
	});

	describe("input event", () => {
		it("shows the selected month and year", async () => {
			const wrapper = mount();
			selects(wrapper)[0].vm.$emit("input", "6");
			selects(wrapper)[1].vm.$emit("input", "2030");
			await wrapper.vm.$nextTick();
			expect(wrapper.vm.month).toBe(6);
			expect(wrapper.vm.year).toBe(2030);
		});
	});
});
