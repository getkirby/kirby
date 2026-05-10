import { describe, it, expect, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { h, ref } from "vue";
import Collapsible from "./Collapsible.vue";

function createObservers() {
	return { resize: { observe: vi.fn(), unobserve: vi.fn() } };
}

function mount(
	props: Record<string, unknown> = {},
	attrs: Record<string, unknown> = {}
) {
	return vueMount(Collapsible, {
		props,
		attrs,
		global: {
			mocks: {
				$panel: { observers: createObservers() }
			}
		}
	});
}

describe("Collapsible.vue", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("renders as the configured element", () => {
			const wrapper = mount({ element: "section" });
			expect(wrapper.element.tagName).toBe("SECTION");
		});
	});

	// slots
	describe("default slot", () => {
		it("renders the default slot children", () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="item">a</span><span class="item">b</span>'
				}
			});
			expect(wrapper.findAll(".item")).toHaveLength(2);
		});

		it("exposes offset and total to the slot", () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: (props: { offset: number; total: number }) =>
						h("span", { class: "info" }, `total=${props.total}`)
				}
			});
			// total is 0 with no rendered items
			expect(wrapper.find(".info").text()).toBe("total=0");
		});
	});

	describe("fallback slot", () => {
		it("does not render fallback when not collapsed", () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="a">A</span>',
					fallback: '<span class="f">F</span>'
				}
			});
			expect(wrapper.find(".f").exists()).toBe(false);
		});

		it("renders fallback marked with data-collapsible-fallback when collapsed", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="a">A</span>',
					fallback: '<span class="f">F</span>'
				}
			});
			wrapper.vm.isCollapsed = true;
			await wrapper.vm.$nextTick();
			const fb = wrapper.find(".f");
			expect(fb.exists()).toBe(true);
			expect(fb.attributes("data-collapsible-fallback")).toBe("");
		});
	});

	// direction
	describe("direction prop", () => {
		const slots = {
			default: '<span class="a">A</span>',
			fallback: '<span class="f">F</span>'
		};

		it("places fallback after items by default (end)", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots
			});
			wrapper.vm.isCollapsed = true;
			await wrapper.vm.$nextTick();
			const children = [...wrapper.element.children];
			expect(children[0].className).toBe("a");
			expect(children[1].className).toBe("f");
		});

		it("places fallback before items when direction=start", async () => {
			const wrapper = vueMount(Collapsible, {
				props: { direction: "start" },
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots
			});
			wrapper.vm.isCollapsed = true;
			await wrapper.vm.$nextTick();
			const children = [...wrapper.element.children];
			expect(children[0].className).toBe("f");
			expect(children[1].className).toBe("a");
		});
	});

	// integration: collapse behavior triggered by calculate()
	describe("collapse behavior", () => {
		// calculate() is async with several awaits. Wait for it to finish.
		async function flush(vm: {
			isUpdating: boolean;
			$nextTick: () => Promise<void>;
		}) {
			for (let i = 0; i < 20 && vm.isUpdating; i++) {
				await vm.$nextTick();
			}
		}

		it("collapses when items overflow and a fallback exists", async () => {
			// jsdom reports width=0 for every element, so any item overflows
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default:
						'<span class="i">A</span><span class="i">B</span><span class="i">C</span>',
					fallback: '<span class="f">F</span>'
				}
			});
			await flush(wrapper.vm);
			expect(wrapper.vm.isCollapsed).toBe(true);
			expect(wrapper.vm.total).toBe(3);
		});

		it("stays expanded when overflowing but no fallback exists", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="i">A</span><span class="i">B</span>'
				}
			});
			await flush(wrapper.vm);
			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.total).toBe(2);
		});

		it("collapses when more children get added than fit", async () => {
			// drive the slot reactively from a parent so we can add items at runtime
			const items = ref(["A", "B"]);
			const wrapper = vueMount(
				{
					render: () =>
						h(Collapsible, null, {
							default: () =>
								items.value.map((t) => h("span", { class: "i", key: t }, t)),
							fallback: () => h("span", { class: "f" }, "F")
						})
				},
				{
					global: { mocks: { $panel: { observers: createObservers() } } }
				}
			);

			const collapsible = wrapper.findComponent(Collapsible);
			await flush(collapsible.vm);

			// container 80px, items 30px, fallback 20px, gap 0
			collapsible.element.getBoundingClientRect = () =>
				({ width: 80 }) as DOMRect;
			vi.spyOn(collapsible.vm, "measure").mockImplementation((el: Element) =>
				el.classList.contains("f") ? 20 : 30
			);
			const realGCS = window.getComputedStyle.bind(window);
			vi.spyOn(window, "getComputedStyle").mockImplementation((el) =>
				el === collapsible.element
					? ({ columnGap: "0", gap: "0" } as CSSStyleDeclaration)
					: realGCS(el)
			);

			// 2 items × 30 = 60 ≤ 80 → fits
			await collapsible.vm.calculate();
			expect(collapsible.vm.isCollapsed).toBe(false);
			expect(collapsible.vm.offset).toBe(2);

			// add a 3rd item → 90 > 80 → must collapse
			items.value = ["A", "B", "C"];
			await collapsible.vm.$nextTick();
			await flush(collapsible.vm);

			expect(collapsible.vm.isCollapsed).toBe(true);
			expect(collapsible.vm.total).toBe(3);

			vi.restoreAllMocks();
		});

		it("expands again once items fit (with stubbed widths)", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="i">A</span><span class="i">B</span>',
					fallback: '<span class="f">F</span>'
				}
			});
			await flush(wrapper.vm);
			// initially collapsed because jsdom has no layout
			expect(wrapper.vm.isCollapsed).toBe(true);

			// pretend the container is 200px wide, items 30px, gap 0
			wrapper.element.getBoundingClientRect = () => ({ width: 200 }) as DOMRect;
			vi.spyOn(wrapper.vm, "measure").mockImplementation((el) =>
				el.classList.contains("f") ? 20 : 30
			);
			const realGCS = window.getComputedStyle.bind(window);
			vi.spyOn(window, "getComputedStyle").mockImplementation((el) =>
				el === wrapper.element
					? ({ columnGap: "0", gap: "0" } as CSSStyleDeclaration)
					: realGCS(el)
			);

			await wrapper.vm.calculate();

			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(2);
			vi.restoreAllMocks();
		});
	});

	// observe / unobserve
	describe("resize observation", () => {
		it("registers with $panel.observers.resize on mount", () => {
			const observers = createObservers();
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers } } }
			});
			expect(observers.resize.observe).toHaveBeenCalledWith(wrapper.element);
		});

		it("unregisters on unmount", () => {
			const observers = createObservers();
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers } } }
			});
			const el = wrapper.element;
			wrapper.unmount();
			expect(observers.resize.unobserve).toHaveBeenCalledWith(el);
		});

		it("recalculates when the element fires a resize event", () => {
			const wrapper = mount();
			const calculate = vi.spyOn(wrapper.vm, "calculate");
			wrapper.element.dispatchEvent(new Event("resize"));
			expect(calculate).toHaveBeenCalledOnce();
		});

		it("skips recalculation when already updating", () => {
			const wrapper = mount();
			wrapper.vm.isUpdating = true;
			const calculate = vi.spyOn(wrapper.vm, "calculate");
			wrapper.element.dispatchEvent(new Event("resize"));
			expect(calculate).not.toHaveBeenCalled();
		});

		it("removes the resize listener on unmount", () => {
			const wrapper = mount();
			const el = wrapper.element;
			wrapper.unmount();
			// after unmount the vm is still accessible;
			// spy on calculate and dispatch
			const calculate = vi.spyOn(wrapper.vm, "calculate");
			el.dispatchEvent(new Event("resize"));
			expect(calculate).not.toHaveBeenCalled();
		});
	});

	// pure helpers
	describe("width method", () => {
		it("returns 0 for count <= 0", () => {
			const vm = mount().vm;
			expect(vm.width([10, 20, 30], 0, 5)).toBe(0);
			expect(vm.width([10, 20, 30], -1, 5)).toBe(0);
		});

		it("uses the first N widths when direction=end", () => {
			const vm = mount({ direction: "end" }).vm;
			// 10 + 20 + 1 gap of 5
			expect(vm.width([10, 20, 30], 2, 5)).toBe(35);
		});

		it("uses the last N widths when direction=start", () => {
			const vm = mount({ direction: "start" }).vm;
			// 20 + 30 + 1 gap of 5
			expect(vm.width([10, 20, 30], 2, 5)).toBe(55);
		});

		it("adds (count - 1) gaps", () => {
			const vm = mount().vm;
			// 3*10 + 2*4
			expect(vm.width([10, 10, 10], 3, 4)).toBe(38);
		});
	});

	describe("growUntilFull method", () => {
		it("fits as many items as the available width allows", () => {
			const wrapper = mount();
			// 10 fits (10 ≤ 35); 10+20+5=35 fits; 10+20+30+10=70 doesn't
			expect(
				wrapper.vm.growUntilFull({
					available: 35,
					gap: 5,
					total: 3,
					widths: [10, 20, 30]
				})
			).toEqual({ offset: 2, total: 3, overflown: true });
		});

		it("returns total when everything fits", () => {
			const wrapper = mount();
			expect(
				wrapper.vm.growUntilFull({
					available: 1000,
					gap: 5,
					total: 3,
					widths: [10, 20, 30]
				})
			).toEqual({ offset: 3, total: 3, overflown: false });
		});

		it("returns 0 when nothing fits", () => {
			const wrapper = mount();
			expect(
				wrapper.vm.growUntilFull({
					available: 5,
					gap: 0,
					total: 2,
					widths: [10, 10]
				})
			).toEqual({ offset: 0, total: 2, overflown: true });
		});
	});
});
