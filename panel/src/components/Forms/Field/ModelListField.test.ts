import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { flushPromises, mount } from "@vue/test-utils";
import ModelListField from "./ModelListField.vue";

const listeners: Record<string, () => void> = {};

const events = {
	emit: vi.fn(),
	off: vi.fn(),
	on: vi.fn((event: string, handler: () => void) => {
		listeners[event] = handler;
	})
};
const api = { get: vi.fn() };
const panel = { error: vi.fn() };

const state = {
	columns: { title: { label: "Title", type: "url" } },
	models: [],
	pagination: { limit: 20, offset: 0, page: 1, total: 0 },
	sortable: true
};

function factory(props = {}) {
	return mount(ModelListField, {
		props: {
			endpoints: { field: "pages/test/fields/drafts" },
			name: "drafts",
			...props
		},
		shallow: true,
		global: {
			mocks: {
				$api: api,
				$events: events,
				$panel: panel
			}
		}
	});
}

/**
 * The arguments of the last emitted event. The instance
 * is compared by identity, as enumerating its keys warns.
 */
function lastEmit() {
	return events.emit.mock.calls.at(-1) ?? [];
}

describe("ModelListField.vue", () => {
	beforeEach(() => {
		api.get.mockReset();
		api.get.mockResolvedValue(state);
		events.emit.mockClear();
		events.off.mockClear();
		events.on.mockClear();
		panel.error.mockClear();
	});

	it("fetches its entries once it is mounted", async () => {
		const wrapper = factory();

		expect(api.get).toHaveBeenCalledWith("pages/test/fields/drafts", {
			page: 1,
			searchterm: null
		});

		await flushPromises();

		expect(wrapper.vm.state).toStrictEqual(state);
		expect(wrapper.vm.isLoading).toBe(false);
	});

	it("shows a skeleton until the entries arrive", async () => {
		const wrapper = factory();

		expect(wrapper.vm.isLoading).toBe(true);
		expect(wrapper.vm.skeleton).toHaveLength(1);
		expect(wrapper.vm.skeleton[0].theme).toBe("skeleton");

		// the entries are unknown, so the list cannot be validated yet
		expect(wrapper.vm.validator).toStrictEqual({ count: 0 });

		await flushPromises();

		expect(wrapper.vm.validator).toStrictEqual({
			count: 0,
			max: undefined,
			min: undefined
		});
	});

	it("keeps the columns from the props while loading", async () => {
		const columns = { title: { label: "Title" } };
		const wrapper = factory({ columns });

		expect(wrapper.vm.state.columns).toStrictEqual(columns);

		await flushPromises();

		// the loaded columns carry the resolved types
		expect(wrapper.vm.state.columns).toStrictEqual(state.columns);
	});

	it("announces itself once the entries have arrived", async () => {
		const wrapper = factory();

		expect(events.emit).not.toHaveBeenCalled();

		await flushPromises();

		expect(events.emit).toHaveBeenCalledTimes(1);
		expect(lastEmit()[0]).toBe("field.loaded");
		expect(lastEmit()[1]).toBe(wrapper.vm);
	});

	it("listens to its refresh events while mounted", async () => {
		const wrapper = factory();
		await flushPromises();

		expect(events.on).toHaveBeenCalledWith("model.update", expect.anything());

		wrapper.unmount();

		expect(events.off).toHaveBeenCalledWith("model.update", expect.anything());
	});

	it("reloads when one of its refresh events fires", async () => {
		factory();
		await flushPromises();
		api.get.mockClear();

		vi.useFakeTimers();

		// a dialog that answers with two events is worth one reload
		listeners["model.update"]();
		listeners["model.update"]();

		await vi.advanceTimersByTimeAsync(1);
		vi.useRealTimers();

		expect(api.get).toHaveBeenCalledOnce();
	});

	it("announces itself again after a reload", async () => {
		const wrapper = factory();
		await flushPromises();

		const reloaded = { ...state, pagination: { page: 2, total: 25 } };
		api.get.mockResolvedValue(reloaded);

		await wrapper.vm.reload({ page: 2 });

		expect(api.get).toHaveBeenLastCalledWith("pages/test/fields/drafts", {
			page: 2,
			searchterm: null
		});

		expect(wrapper.vm.state).toStrictEqual(reloaded);

		// once after the initial load, once after the reload
		expect(events.emit).toHaveBeenCalledTimes(2);
		expect(lastEmit()[0]).toBe("field.loaded");
		expect(lastEmit()[1]).toBe(wrapper.vm);
	});

	it("announces itself even when the load fails", async () => {
		const error = new Error("Nope");
		api.get.mockRejectedValue(error);

		const wrapper = factory();
		await flushPromises();

		expect(panel.error).toHaveBeenCalledWith(error);
		expect(wrapper.vm.isLoading).toBe(false);

		// the list must not look empty when it could not be loaded
		expect(wrapper.vm.emptyProps).toStrictEqual({
			icon: "alert",
			text: "Nope"
		});

		expect(wrapper.vm.isProcessing).toBe(false);
		expect(events.emit).toHaveBeenCalledOnce();
		expect(lastEmit()[0]).toBe("field.loaded");
		expect(lastEmit()[1]).toBe(wrapper.vm);
	});
});
