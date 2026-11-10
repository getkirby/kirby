import { describe, it, expect, onTestFinished, vi } from "@test/unit";
import { mount as vueMount, type VueWrapper } from "@vue/test-utils";
import { h, ref } from "vue";
import preserveListeners from "@/mixins/preserveListeners";
import Collapsible from "./Collapsible.vue";

function createObservers() {
	return { resize: { observe: vi.fn(), unobserve: vi.fn() } };
}

/**
 * calculate() is async with several awaits. Wait for it to finish.
 */
async function flush(vm: {
	isUpdating: boolean;
	$nextTick: () => Promise<void>;
}) {
	for (let i = 0; i < 20 && vm.isUpdating; i++) {
		await vm.$nextTick();
	}
}

/**
 * Fakes the widths happy-dom cannot measure:
 * items 30px (wide ones 50px), fallback 20px, gap 0
 */
function layout(wrapper: VueWrapper, container: number) {
	wrapper.element.getBoundingClientRect = () =>
		({ width: container }) as DOMRect;

	const measure = vi
		.spyOn(wrapper.vm as unknown as { measure(el: Element): number }, "measure")
		.mockImplementation((el: Element) => {
			if (el.classList.contains("f")) {
				return 20;
			}

			return el.classList.contains("w") ? 50 : 30;
		});

	const realGCS = window.getComputedStyle.bind(window);
	const gcs = vi
		.spyOn(window, "getComputedStyle")
		.mockImplementation((el) =>
			el === wrapper.element
				? ({ columnGap: "0", gap: "0" } as CSSStyleDeclaration)
				: realGCS(el)
		);

	onTestFinished(() => {
		measure.mockRestore();
		gcs.mockRestore();
	});
}

/**
 * Mounts items with the given classes and a fallback
 * that renders the offset and total it receives
 */
function collapse(
	props: Record<string, unknown> = {},
	items = ["w", "i", "i"]
) {
	return vueMount(Collapsible, {
		props,
		global: { mocks: { $panel: { observers: createObservers() } } },
		slots: {
			default: () => items.map((item) => h("span", { class: item })),
			fallback: ({ offset, total }: { offset: number; total: number }) =>
				h("span", { class: "f" }, `${offset}/${total}`)
		}
	});
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

describe("k-collapsible", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);
	});

	// props
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

		it("fits the first items next to the fallback by default (end)", async () => {
			const wrapper = collapse();
			await flush(wrapper.vm);
			layout(wrapper, 80);
			await wrapper.vm.calculate();

			// 50 + 20 fits, 50 + 30 + 20 doesn't
			expect(wrapper.find(".f").text()).toBe("1/3");
		});

		it("fits the last items next to the fallback when direction=start", async () => {
			const wrapper = collapse({ direction: "start" });
			await flush(wrapper.vm);
			layout(wrapper, 80);
			await wrapper.vm.calculate();

			// 30 + 30 + 20 fits, 50 + 30 + 30 + 20 doesn't
			expect(wrapper.find(".f").text()).toBe("2/3");
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

	describe("element prop", () => {
		it("renders as the configured element", () => {
			const wrapper = mount({ element: "section" });
			expect(wrapper.element.tagName).toBe("SECTION");
		});
	});

	// methods
	describe("calculate()", () => {
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
			layout(collapsible, 80);

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
		});

		it("shows as many items as fit next to the fallback", async () => {
			const wrapper = collapse({}, ["i", "i", "i"]);
			await flush(wrapper.vm);
			layout(wrapper, 70);
			await wrapper.vm.calculate();

			// 30 + 20 fits, 30 + 30 + 20 doesn't
			expect(wrapper.find(".f").text()).toBe("1/3");
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

			layout(wrapper, 200);
			await wrapper.vm.calculate();

			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(2);
		});
	});

	describe("growUntilFull()", () => {
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

	describe("observe()", () => {
		it("registers with $panel.observers.resize on mount", () => {
			const observers = createObservers();
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers } } }
			});
			expect(observers.resize.observe).toHaveBeenCalledWith(wrapper.element);
		});
	});

	describe("onResize()", () => {
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
	});

	describe("unobserve()", () => {
		it("unregisters on unmount", () => {
			const observers = createObservers();
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers } } }
			});
			const el = wrapper.element;
			wrapper.unmount();
			expect(observers.resize.unobserve).toHaveBeenCalledWith(el);
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

	describe("width()", () => {
		it("returns 0 for count <= 0", () => {
			const wrapper = mount();
			expect(wrapper.vm.width([10, 20, 30], 0, 5)).toBe(0);
			expect(wrapper.vm.width([10, 20, 30], -1, 5)).toBe(0);
		});

		it("uses the first N widths when direction=end", () => {
			const wrapper = mount({ direction: "end" });
			// 10 + 20 + 1 gap of 5
			expect(wrapper.vm.width([10, 20, 30], 2, 5)).toBe(35);
		});

		it("uses the last N widths when direction=start", () => {
			const wrapper = mount({ direction: "start" });
			// 20 + 30 + 1 gap of 5
			expect(wrapper.vm.width([10, 20, 30], 2, 5)).toBe(55);
		});

		it("adds (count - 1) gaps", () => {
			const wrapper = mount();
			// 3*10 + 2*4
			expect(wrapper.vm.width([10, 10, 10], 3, 4)).toBe(38);
		});
	});

	// events
	describe("click event", () => {
		it("calls the listener once", async () => {
			const handler = vi.fn();
			const wrapper = vueMount(Collapsible, {
				attrs: { onClick: handler },
				global: {
					mixins: [preserveListeners],
					mocks: { $panel: { observers: createObservers() } }
				}
			});
			await wrapper.trigger("click");
			expect(handler).toHaveBeenCalledOnce();
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

		it("exposes offset and total to the slot", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: ({ offset, total }: { offset: number; total: number }) =>
						["A", "B", "C"].map((key) =>
							h("span", { class: "info", key }, `${offset}/${total}`)
						)
				}
			});
			await flush(wrapper.vm);

			// happy-dom has no layout, so none of the 3 items fits
			expect(wrapper.find(".info").text()).toBe("0/3");
		});
	});

	describe("fallback slot", () => {
		it("does not render fallback when not collapsed", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="a">A</span>',
					fallback: '<span class="f">F</span>'
				}
			});
			await flush(wrapper.vm);
			layout(wrapper, 200);
			wrapper.element.dispatchEvent(new Event("resize"));
			await flush(wrapper.vm);

			expect(wrapper.find(".a").exists()).toBe(true);
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
});
