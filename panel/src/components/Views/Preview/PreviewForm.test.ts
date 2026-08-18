import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import PreviewForm from "./PreviewForm.vue";

const events = { off: vi.fn(), on: vi.fn() };
const panel = {
	config: { debug: false },
	view: { path: "/pages/test" }
};

function factory() {
	return mount(PreviewForm, {
		props: {
			api: "pages/test",
			blueprint: "default",
			content: {},
			tab: { name: "main" },
			tabs: [{ name: "main" }]
		},
		shallow: true,
		global: {
			mocks: {
				$events: events,
				$panel: panel
			}
		}
	});
}

type Link = HTMLAnchorElement & {
	__vue__: { onClick: (event: Event) => void; to?: string };
};

/**
 * Creates a fake field or section instance with links
 * for the given urls, wrapped in item titles
 */
function loaded(...urls: (string | undefined)[]) {
	const $el = document.createElement("div");
	const links = urls.map((to) => {
		const title = document.createElement("p");
		title.className = "k-item-title";

		const link = document.createElement("a") as Link;
		link.className = "k-link";
		link.__vue__ = { onClick: original, to };

		title.append(link);
		$el.append(title);

		return link;
	});

	return { $el, links };
}

const original = vi.fn();

describe("PreviewForm.vue", () => {
	beforeEach(() => {
		events.off.mockClear();
		events.on.mockClear();
		original.mockClear();
	});

	it("listens to loaded fields and sections while mounted", () => {
		const wrapper = factory();

		expect(events.on).toHaveBeenCalledWith("field.loaded", wrapper.vm.fixLinks);
		expect(events.on).toHaveBeenCalledWith(
			"section.loaded",
			wrapper.vm.fixLinks
		);

		const fixLinks = wrapper.vm.fixLinks;
		wrapper.unmount();

		expect(events.off).toHaveBeenCalledWith("field.loaded", fixLinks);
		expect(events.off).toHaveBeenCalledWith("section.loaded", fixLinks);
	});

	it("redirects page links to the preview form", () => {
		const wrapper = factory();
		const { $el, links } = loaded("/pages/test+child");

		wrapper.vm.fixLinks({ $el });

		const event = { preventDefault: vi.fn() };
		links[0].__vue__.onClick(event as unknown as Event);

		expect(event.preventDefault).toHaveBeenCalled();
		expect(original).not.toHaveBeenCalled();
		expect(wrapper.emitted("navigate")).toStrictEqual([
			["/pages/test+child/preview/form"]
		]);
	});

	it("redirects all page links of the element", () => {
		const wrapper = factory();
		const { $el, links } = loaded("/pages/a", "/pages/b");

		wrapper.vm.fixLinks({ $el });

		for (const link of links) {
			link.__vue__.onClick({ preventDefault: vi.fn() } as unknown as Event);
		}

		expect(wrapper.emitted("navigate")).toStrictEqual([
			["/pages/a/preview/form"],
			["/pages/b/preview/form"]
		]);
	});

	it("keeps links that don't point to a page view", () => {
		const wrapper = factory();
		const { $el, links } = loaded(
			"/pages/test+child/files/test.jpg",
			"/users/test",
			undefined
		);

		wrapper.vm.fixLinks({ $el });

		for (const link of links) {
			expect(link.__vue__.onClick).toBe(original);

			link.__vue__.onClick({ preventDefault: vi.fn() } as unknown as Event);
		}

		expect(original).toHaveBeenCalledTimes(3);
		expect(wrapper.emitted("navigate")).toBeUndefined();
	});

	it("ignores links outside of item titles", () => {
		const wrapper = factory();
		const { $el, links } = loaded("/pages/test+child");

		// move the link out of the item title
		$el.append(links[0]);

		wrapper.vm.fixLinks({ $el });

		expect(links[0].__vue__.onClick).toBe(original);
	});
});
