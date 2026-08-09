import { afterAll, beforeAll, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import dayjs from "@/libraries/dayjs";
import DateInput from "./DateInput.vue";

function mount(props: Record<string, unknown> = {}) {
	return vueMount(DateInput, {
		props: props as never,
		global: {
			mocks: {
				$library: { dayjs },
				$t: (key: string) => key
			},
			directives: { direction: {} }
		}
	});
}

async function type(wrapper: ReturnType<typeof mount>, value: string) {
	const input = wrapper.find("input");
	await input.setValue(value);
	await input.trigger("blur");
	return input.element as HTMLInputElement;
}

function emitted(wrapper: ReturnType<typeof mount>) {
	const events = wrapper.emitted("input");
	return events?.[events.length - 1]?.[0];
}

async function press(
	wrapper: ReturnType<typeof mount>,
	key: string,
	options: Record<string, unknown> = {}
) {
	await wrapper.find("input").trigger("keydown", { key, ...options });
	await wrapper.vm.$nextTick();
	await wrapper.vm.$nextTick();
}

function selected(wrapper: ReturnType<typeof mount>) {
	const input = wrapper.find("input").element as HTMLInputElement;
	return input.value.slice(input.selectionStart!, input.selectionEnd!);
}

describe("DateInput.vue", () => {
	beforeAll(() => {
		vi.useFakeTimers();
		vi.setSystemTime(new Date(2022, 0, 15));
	});

	afterAll(() => {
		vi.useRealTimers();
	});

	// $el
	describe("element", () => {
		it.rendersAs(mount, "INPUT", "k-date-input");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
		it.inheritsNoAttrs(mount);
	});

	describe("placeholder", () => {
		it("shows an example value", () => {
			const input = mount({ display: "MMMM D, YYYY" }).find("input");
			expect(input.attributes("placeholder")).toBe("January 15, 2022");
		});

		it("follows the display pattern", () => {
			expect(
				mount({ display: "DD.MM.YYYY" }).find("input").attributes("placeholder")
			).toBe("15.01.2022");
		});

		it("uses `min` when now is before it", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				min: "2024-06-23"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("23.06.2024");
		});

		it("uses `max` when now is after it", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				max: "2020-02-29"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("29.02.2020");
		});

		it("stays on now when it is inside the boundaries", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				min: "2020-01-01",
				max: "2024-12-31"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("15.01.2022");
		});

		it("uses a boundary that carries a time part", () => {
			const input = mount({
				display: "DD.MM.YYYY",
				min: "2024-06-23 08:30:00"
			}).find("input");

			expect(input.attributes("placeholder")).toBe("23.06.2024");
		});
	});

	describe("parse()", () => {
		it("reads input in the display pattern, not day-first", async () => {
			// https://github.com/getkirby/kirby/issues/7342
			const wrapper = mount({ display: "MM-DD-YYYY" });
			await type(wrapper, "06-26-2025");
			expect(emitted(wrapper)).toBe("2025-06-26");
		});

		it("accepts any separator and unpadded numbers", async () => {
			const wrapper = mount({ display: "MM-DD-YYYY" });
			await type(wrapper, "6/26/2025");
			expect(emitted(wrapper)).toBe("2025-06-26");
		});

		it("parses a pattern without a day part", async () => {
			// https://github.com/getkirby/kirby/issues/6408
			const wrapper = mount({ display: "MM/YYYY" });
			await type(wrapper, "08/2025");
			expect(emitted(wrapper)).toBe("2025-08-01");
		});

		it("resolves ambiguous input via the display pattern", async () => {
			const monthFirst = mount({ display: "MM/DD/YYYY" });
			await type(monthFirst, "05-03-2021");
			expect(emitted(monthFirst)).toBe("2021-05-03");

			const dayFirst = mount({ display: "DD.MM.YYYY" });
			await type(dayFirst, "05-03-2021");
			expect(emitted(dayFirst)).toBe("2021-03-05");
		});

		it("still falls back to interpret() for partial input", async () => {
			const wrapper = mount({ display: "DD.MM.YYYY" });
			await type(wrapper, "5");
			expect(emitted(wrapper)).toBe("2022-01-05");
		});

		it("reads a display pattern that escapes a literal", async () => {
			const wrapper = mount({ display: "[Am] D. MMMM YYYY" });
			const input = await type(wrapper, "Am 5. March 2021");

			expect(emitted(wrapper)).toBe("2021-03-05");
			expect(input.value).toBe("Am 5. March 2021");
		});
	});

	describe("onArrowUp()", () => {
		it("alters the selected part", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input").element as HTMLInputElement;

			// select the day
			input.setSelectionRange(0, 2);
			await press(wrapper, "ArrowUp");

			expect(input.value).toBe("14 July 2026");
			expect(selected(wrapper)).toBe("14");
		});

		it("keeps the part selected when the rendering grows", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input").element as HTMLInputElement;

			// select the month
			input.setSelectionRange(3, 7);
			await press(wrapper, "ArrowUp");

			// the longer month name moves everything behind it
			expect(input.value).toBe("13 August 2026");
			expect(selected(wrapper)).toBe("August");
		});
	});

	describe("onBlur()", () => {
		it("does not re-parse an untouched rendering of the value", async () => {
			// https://github.com/getkirby/kirby/issues/7342: a calendar-picked
			// value used to be corrupted by blurring the input it rendered into
			const wrapper = mount({
				display: "MM-DD-YYYY",
				value: "2025-06-26"
			});
			const input = wrapper.find("input");
			expect((input.element as HTMLInputElement).value).toBe("06-26-2025");

			await input.trigger("blur");

			expect(wrapper.emitted("input")).toBeUndefined();
			expect((input.element as HTMLInputElement).value).toBe("06-26-2025");
		});

		it("keeps unparseable text and flags the input as invalid", async () => {
			// https://github.com/getkirby/kirby/issues/6309
			const wrapper = mount({ display: "DD.MM.YYYY" });
			const input = await type(wrapper, "2. März 2024");

			// the text stays as it was typed, nothing is committed
			expect(input.value).toBe("2. März 2024");
			expect(wrapper.emitted("input")).toBeUndefined();
			expect(input.validationMessage).toBe("error.validation.date");
		});

		it("commits an empty string for empty input", async () => {
			const wrapper = mount({
				display: "DD.MM.YYYY",
				value: "2021-03-05"
			});
			await type(wrapper, "");
			expect(emitted(wrapper)).toBe("");
		});

		it("clears the invalid state once the input parses again", async () => {
			const wrapper = mount({ display: "DD.MM.YYYY" });
			const input = await type(wrapper, "not a date");
			expect(input.validationMessage).toBe("error.validation.date");

			await type(wrapper, "05.03.2021");
			expect(emitted(wrapper)).toBe("2021-03-05");
			expect(input.validationMessage).toBe("");
		});

		it("shows what the parsed value renders into", async () => {
			// even when the rendering itself did not change and the
			// input therefore does not re-render
			const wrapper = mount({ display: "DD.MM.YYYY", value: "2021-03-05" });
			const input = await type(wrapper, "5.3.2021");
			expect(input.value).toBe("05.03.2021");
		});
	});

	describe("onEnter()", () => {
		it("commits and submits parseable input", async () => {
			const wrapper = mount({ display: "DD.MM.YYYY" });
			const input = wrapper.find("input");

			await input.setValue("5.3.2021");
			await input.trigger("keydown", { key: "Enter" });
			await wrapper.vm.$nextTick();

			expect(emitted(wrapper)).toBe("2021-03-05");
			expect(wrapper.emitted("submit")).toHaveLength(1);
		});

		it("does not submit what could not be parsed", async () => {
			const wrapper = mount({ display: "DD.MM.YYYY", value: "2021-03-05" });
			const input = wrapper.find("input");

			await input.setValue("not a date");
			await input.trigger("keydown", { key: "Enter" });
			await wrapper.vm.$nextTick();

			expect(wrapper.emitted("input")).toBeUndefined();
			expect(wrapper.emitted("submit")).toBeUndefined();
		});
	});

	describe("onTab()", () => {
		it("selects the parts of the rendered value", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input").element as HTMLInputElement;

			expect(input.value).toBe("13 July 2026");

			// place the cursor in the first part
			input.setSelectionRange(0, 0);

			for (const part of ["13", "July", "2026"]) {
				await press(wrapper, "Tab");
				expect(selected(wrapper)).toBe(part);
			}
		});

		it("selects the parts backwards on shift + tab", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input").element as HTMLInputElement;

			// select the last part
			input.setSelectionRange(8, 12);

			for (const part of ["July", "13"]) {
				await press(wrapper, "Tab", { shiftKey: true });
				expect(selected(wrapper)).toBe(part);
			}
		});

		it("selects the part the cursor sits in", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input").element as HTMLInputElement;

			// place the cursor inside the month name
			input.setSelectionRange(5, 5);
			await press(wrapper, "Tab");

			expect(selected(wrapper)).toBe("July");
		});

		it("selects no part while the input cannot be parsed", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input");
			const el = input.element as HTMLInputElement;

			await input.setValue("not a date");
			el.setSelectionRange(0, 0);
			await press(wrapper, "Tab");

			expect(selected(wrapper)).toBe("");
		});

		it("keeps the last part selected", async () => {
			const wrapper = mount({ display: "D MMMM YYYY", value: "2026-07-13" });
			const input = wrapper.find("input").element as HTMLInputElement;

			// select the last part, tab moves the focus out of the input
			input.setSelectionRange(8, 12);
			await press(wrapper, "Tab");

			expect(selected(wrapper)).toBe("2026");
		});
	});
});
