import { describe, it, expect, onTestFinished, vi } from "@test/unit";
import { mount as vueMount, type VueWrapper } from "@vue/test-utils";
import { h, ref } from "vue";
import preserveListeners from "@/mixins/preserveListeners";
import Collapsible from "./Collapsible.vue";

function createObservers() {
	return { resize: { observe: vi.fn(), unobserve: vi.fn() } };
}

/**
 * calculate() awaits several renders. Wait for it to finish.
 */
async function flush(vm: {
	isMeasuring: boolean;
	$nextTick: () => Promise<void>;
}) {
	for (let i = 0; i < 20 && vm.isMeasuring; i++) {
		await vm.$nextTick();
	}
}

/**
 * Fakes the widths happy-dom cannot measure: items 30px,
 * wide ones 50px, shrinking ones 50px or 40px next to
 * the fallback, the fallback 20px
 */
function layout(wrapper: VueWrapper, gap = 0) {
	const vm = wrapper.vm as unknown as {
		isCollapsed: boolean;
		measure(el: Element): number;
	};

	const measure = vi.spyOn(vm, "measure").mockImplementation((el: Element) => {
		if (el.classList.contains("w")) {
			return 50;
		}

		if (el.classList.contains("s")) {
			return vm.isCollapsed ? 40 : 50;
		}

		return el.classList.contains("f") ? 20 : 30;
	});

	const realGCS = window.getComputedStyle.bind(window);
	const gcs = vi
		.spyOn(window, "getComputedStyle")
		.mockImplementation((el) =>
			el === wrapper.element
				? ({ columnGap: gap + "px", gap: gap + "px" } as CSSStyleDeclaration)
				: realGCS(el)
		);

	onTestFinished(() => {
		measure.mockRestore();
		gcs.mockRestore();
	});
}

/**
 * Reports a new content box width like `$panel.observers.resize`
 */
async function resize(wrapper: VueWrapper, width: number) {
	wrapper.element.dispatchEvent(
		new CustomEvent("resize", { detail: { width } })
	);
	await flush(wrapper.vm as unknown as Parameters<typeof flush>[0]);
}

/**
 * Mounts items with the given classes, of which it renders
 * the `offset` ones like a consumer, and a fallback
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
			default: ({ offset }: { offset: number }) =>
				visible(items, offset, props.direction).map((item) =>
					h("span", { class: item })
				),
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

/**
 * Returns the `offset` items that stay visible
 */
function visible<T>(items: T[], offset: number, direction: unknown = "end") {
	const start = direction === "start" ? Math.max(items.length - offset, 0) : 0;
	return items.slice(start, start + offset);
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
			layout(wrapper);
			await resize(wrapper, 80);

			// 50 + 20 fits, 50 + 30 + 20 doesn't
			expect(wrapper.find(".f").text()).toBe("1/3");
		});

		it("fits the last items next to the fallback when direction=start", async () => {
			const wrapper = collapse({ direction: "start" });
			layout(wrapper);
			await resize(wrapper, 80);

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

	// lifecycle
	describe("updated hook", () => {
		it("recalculates when the slot content changes", async () => {
			// drive the slot reactively from a parent so we can add items at runtime
			const items = ref(["A", "B"]);
			const wrapper = vueMount(
				{
					render: () =>
						h(Collapsible, null, {
							default: ({ offset }: { offset: number }) =>
								visible(items.value, offset).map((t) =>
									h("span", { class: "i", key: t }, t)
								),
							fallback: () => h("span", { class: "f" }, "F")
						})
				},
				{
					global: { mocks: { $panel: { observers: createObservers() } } }
				}
			);

			const collapsible = wrapper.findComponent(Collapsible);
			layout(collapsible);

			// 2 × 30 = 60 ≤ 80 → fits
			await resize(collapsible, 80);
			expect(collapsible.vm.isCollapsed).toBe(false);
			expect(collapsible.vm.offset).toBe(2);

			// a 3rd item → 90 > 80 → must collapse
			items.value = ["A", "B", "C"];
			await collapsible.vm.$nextTick();
			await flush(collapsible.vm);

			expect(collapsible.vm.isCollapsed).toBe(true);
			expect(collapsible.vm.total).toBe(3);
		});
	});

	// methods
	describe("calculate()", () => {
		it("waits for the width from the observer", async () => {
			const wrapper = collapse();
			await wrapper.vm.calculate();
			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(Infinity);
		});

		it("does nothing while measuring", async () => {
			const wrapper = collapse();
			wrapper.vm.isMeasuring = true;
			await resize(wrapper, 0);
			expect(wrapper.vm.offset).toBe(Infinity);
		});

		it("shows all items when they fit", async () => {
			const wrapper = collapse();
			layout(wrapper);
			await resize(wrapper, 110);

			// 50 + 30 + 30 fits
			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(3);
			expect(wrapper.find(".f").exists()).toBe(false);
		});

		it("collapses when items overflow and a fallback exists", async () => {
			const wrapper = collapse();
			layout(wrapper);
			await resize(wrapper, 0);
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
			layout(wrapper);
			await resize(wrapper, 40);

			// 30 fits, 30 + 30 doesn't
			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(1);
			expect(wrapper.vm.total).toBe(2);
		});

		it("shows as many items as fit next to the fallback", async () => {
			const wrapper = collapse({}, ["i", "i", "i"]);
			layout(wrapper);
			await resize(wrapper, 70);

			// 30 + 20 fits, 30 + 30 + 20 doesn't
			expect(wrapper.find(".f").text()).toBe("1/3");
		});

		it("counts the gaps between items and fallback", async () => {
			const wrapper = collapse({}, ["i", "i", "i"]);
			layout(wrapper, 5);
			await resize(wrapper, 85);

			// 30 + 5 + 20 fits, 30 + 5 + 30 + 5 + 20 doesn't
			expect(wrapper.find(".f").text()).toBe("1/3");
		});

		it("hides more items when the fallback grows", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: ({ offset }: { offset: number }) =>
						visible(["i", "i", "i", "i"], offset).map((i) =>
							h("span", { class: i })
						),
					// 50px wide when at most two items are shown
					fallback: ({ offset, total }: { offset: number; total: number }) =>
						h("span", { class: offset > 2 ? "f" : "f w" }, `${offset}/${total}`)
				}
			});
			layout(wrapper);
			await resize(wrapper, 90);

			// 30 + 30 + 30 + 20 and 30 + 30 + 50 don't fit, 30 + 50 does
			expect(wrapper.find(".f").text()).toBe("1/4");
		});

		it("keeps an item that shrinks next to the fallback", async () => {
			const wrapper = collapse({}, ["s", "i"]);
			layout(wrapper);
			await resize(wrapper, 60);

			// 50 + 20 doesn't fit, but the item shrinks to 40
			expect(wrapper.find(".f").text()).toBe("1/2");
		});

		it("tolerates subpixel rounding", async () => {
			const wrapper = collapse({}, ["i", "i", "i"]);
			layout(wrapper);
			await resize(wrapper, 89.6);

			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(3);
		});

		it("expands again once items fit", async () => {
			const wrapper = collapse();
			layout(wrapper);
			await resize(wrapper, 0);
			expect(wrapper.vm.isCollapsed).toBe(true);

			await resize(wrapper, 200);
			expect(wrapper.vm.isCollapsed).toBe(false);
			expect(wrapper.vm.offset).toBe(3);
		});
	});

	describe("fit()", () => {
		it("fits as many items as the space allows", () => {
			const wrapper = mount();
			// 10 + 5 + 20 = 35 fits, 10 + 5 + 20 + 5 + 30 doesn't
			expect(wrapper.vm.fit([10, 20, 30], 5, 35)).toBe(2);
		});

		it("fits all items into enough space", () => {
			const wrapper = mount();
			expect(wrapper.vm.fit([10, 20, 30], 5, 1000)).toBe(3);
		});

		it("fits no item into too little space", () => {
			const wrapper = mount();
			expect(wrapper.vm.fit([10, 10], 0, 5)).toBe(0);
		});

		it("fits the last items when direction=start", () => {
			const wrapper = mount({ direction: "start" });
			// 10 + 5 + 20 = 35 fits, 30 + 5 + 20 + 5 + 10 doesn't
			expect(wrapper.vm.fit([30, 20, 10], 5, 35)).toBe(2);
		});
	});

	describe("measure()", () => {
		it("adds the margins to the rendered width", () => {
			const wrapper = mount();
			const el = document.createElement("span");
			el.style.margin = "0 5px 0 3px";
			document.body.append(el);
			onTestFinished(() => el.remove());
			el.getBoundingClientRect = () => ({ width: 30 }) as DOMRect;
			expect(wrapper.vm.measure(el)).toBe(38);
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
		it("recalculates with the new width", async () => {
			const wrapper = mount();
			const calculate = vi.spyOn(wrapper.vm, "calculate");
			await resize(wrapper, 80);
			expect(calculate).toHaveBeenCalled();
			expect(wrapper.vm.available).toBe(80);
		});

		it("ignores a resize that keeps the width", async () => {
			const wrapper = mount();
			await resize(wrapper, 80);
			const calculate = vi.spyOn(wrapper.vm, "calculate");
			await resize(wrapper, 80);
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
			el.dispatchEvent(new CustomEvent("resize", { detail: { width: 80 } }));
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
			layout(wrapper);
			await resize(wrapper, 70);

			// 30 + 30 fits, 30 + 30 + 30 doesn't
			expect(wrapper.find(".info").text()).toBe("2/3");
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
			layout(wrapper);
			await resize(wrapper, 200);

			expect(wrapper.find(".a").exists()).toBe(true);
			expect(wrapper.find(".f").exists()).toBe(false);
		});

		it("renders fallback when collapsed", async () => {
			const wrapper = vueMount(Collapsible, {
				global: { mocks: { $panel: { observers: createObservers() } } },
				slots: {
					default: '<span class="a">A</span>',
					fallback: '<span class="f">F</span>'
				}
			});
			wrapper.vm.isCollapsed = true;
			await wrapper.vm.$nextTick();
			expect(wrapper.find(".f").exists()).toBe(true);
		});
	});
});
