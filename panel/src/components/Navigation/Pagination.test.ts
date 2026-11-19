import { describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount as vueMount } from "@vue/test-utils";
import Pagination from "./Pagination.vue";

const Button = {
	name: "k-button",
	props: ["disabled", "icon", "size", "text", "title", "type", "variant"],
	emits: ["click"],
	template: `<button :type="type" @click="$emit('click', $event)" />`
};
const ButtonGroup = {
	name: "k-button-group",
	props: ["layout"],
	template: "<div><slot /></div>"
};
const Dropdown = {
	name: "k-dropdown",
	props: ["alignX"],
	methods: { close: vi.fn(), toggle: vi.fn() },
	template: "<div><slot /></div>"
};

function mount(props = {}, attrs = {}) {
	return vueMount(Pagination, {
		props: { limit: 10, page: 2, total: 25, ...props },
		attrs,
		global: {
			stubs: {
				"k-button": Button,
				"k-button-group": ButtonGroup,
				"k-dropdown": Dropdown
			}
		}
	});
}

function button(wrapper: ReturnType<typeof mount>, icon: string) {
	return wrapper
		.findAllComponents(Button)
		.find((button) => button.props("icon") === icon)!;
}

function details(wrapper: ReturnType<typeof mount>) {
	return wrapper
		.findAllComponents(Button)
		.find((button) => button.classes("k-pagination-details"))!;
}

function lastPaginate(wrapper: ReturnType<typeof mount>) {
	return wrapper.emitted("paginate")?.at(-1)?.[0];
}

describe("k-pagination", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-pagination");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders a collapsed group with prev and next", () => {
			const wrapper = mount();
			expect(wrapper.findComponent(ButtonGroup).props("layout")).toBe(
				"collapsed"
			);
			expect(button(wrapper, "angle-left").props()).toMatchObject({
				disabled: false,
				size: "xs",
				title: "prev",
				variant: "filled"
			});
			expect(button(wrapper, "angle-right").props("title")).toBe("next");
		});
	});

	// props
	describe("details prop", () => {
		it("shows the range and total", () => {
			const wrapper = mount({ details: true });
			expect(wrapper.find(".k-pagination-details").exists()).toBe(true);
			expect(details(wrapper).props("text")).toBe("11-20 / 25");
		});

		it("shows a single item per page", () => {
			const wrapper = mount({ details: true, limit: 1, page: 3, total: 5 });
			expect(details(wrapper).props("text")).toBe("3 / 5");
		});

		it("offers a page selector", () => {
			const wrapper = mount({ details: true });
			const options = wrapper.findAll("select option");
			expect(options.map((option) => option.text())).toStrictEqual([
				"1",
				"2",
				"3"
			]);
			expect((options[1].element as HTMLOptionElement).selected).toBe(true);
		});

		it("has no details by default", () => {
			expect(mount().find(".k-pagination-details").exists()).toBe(false);
		});
	});

	describe("limit prop", () => {
		it("splits the items into pages", () => {
			const wrapper = mount({ details: true, limit: 5 });
			expect(wrapper.findAll("select option")).toHaveLength(5);
		});
	});

	describe("page prop", () => {
		it("disables prev on the first page", () => {
			const wrapper = mount({ page: 1 });
			expect(button(wrapper, "angle-left").props("disabled")).toBe(true);
			expect(button(wrapper, "angle-right").props("disabled")).toBe(false);
		});

		it("disables next on the last page", () => {
			const wrapper = mount({ page: 3 });
			expect(button(wrapper, "angle-left").props("disabled")).toBe(false);
			expect(button(wrapper, "angle-right").props("disabled")).toBe(true);
		});
	});

	describe("total prop", () => {
		it("renders nothing for a single page", () => {
			expect(mount({ total: 10 }).html()).toBe("<!--v-if-->");
		});
	});

	describe("validate prop", () => {
		it("paginates once validated", async () => {
			const validate = vi.fn(() => Promise.resolve());
			const wrapper = mount({ validate });
			await wrapper.vm.goTo(3);
			expect(validate).toHaveBeenCalledWith(3);
			expect(lastPaginate(wrapper)).toMatchObject({ page: 3 });
		});

		it("stops when the validation fails", async () => {
			const wrapper = mount({ validate: () => Promise.reject() });
			await wrapper.vm.goTo(3);
			expect(wrapper.emitted("paginate")).toBeUndefined();
		});
	});

	// methods
	describe("goTo()", () => {
		it("emits the page with its range", async () => {
			const wrapper = mount();
			await wrapper.vm.goTo(3);
			expect(lastPaginate(wrapper)).toStrictEqual({
				end: 25,
				limit: 10,
				offset: 20,
				page: 3,
				start: 21,
				total: 25
			});
		});

		it("keeps the page within the pages", async () => {
			const wrapper = mount({ page: 1 });
			await wrapper.vm.goTo(9);
			expect(lastPaginate(wrapper)).toMatchObject({ page: 3 });
		});

		it("closes the page selector", async () => {
			const wrapper = mount({ details: true });
			const close = vi.spyOn(wrapper.findComponent(Dropdown).vm, "close");
			await wrapper.vm.goTo(3);
			expect(close).toHaveBeenCalledOnce();
		});
	});

	describe("next()", () => {
		it("goes to the next page", async () => {
			const wrapper = mount();
			wrapper.vm.next();
			await flushPromises();
			expect(lastPaginate(wrapper)).toMatchObject({ page: 3, start: 21 });
		});
	});

	describe("prev()", () => {
		it("goes to the previous page", async () => {
			const wrapper = mount();
			wrapper.vm.prev();
			await flushPromises();
			expect(lastPaginate(wrapper)).toMatchObject({ page: 1, start: 1 });
		});
	});

	// events
	describe("events", () => {
		it("paginates with the buttons", async () => {
			const wrapper = mount();
			await button(wrapper, "angle-right").trigger("click");
			await flushPromises();
			await button(wrapper, "angle-left").trigger("click");
			await flushPromises();
			expect(
				wrapper
					.emitted("paginate")
					?.map(([event]) => (event as { page: number }).page)
			).toStrictEqual([3, 1]);
		});

		it("paginates with the arrow keys", async () => {
			const wrapper = mount();
			await wrapper.trigger("keydown", { key: "ArrowRight" });
			await flushPromises();
			await wrapper.trigger("keydown", { key: "ArrowLeft" });
			await flushPromises();
			expect(
				wrapper
					.emitted("paginate")
					?.map(([event]) => (event as { page: number }).page)
			).toStrictEqual([3, 1]);
		});

		it("toggles the page selector", async () => {
			const wrapper = mount({ details: true });
			const toggle = vi.spyOn(wrapper.findComponent(Dropdown).vm, "toggle");
			await details(wrapper).trigger("click");
			expect(toggle).toHaveBeenCalledOnce();
		});

		it("goes to the selected page", async () => {
			const wrapper = mount({ details: true });
			await wrapper.find("select").setValue("3");
			await wrapper.find("form").trigger("submit");
			await flushPromises();
			expect(lastPaginate(wrapper)).toMatchObject({ page: 3 });
		});
	});
});
