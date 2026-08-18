import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount } from "@vue/test-utils";
import ModelListField from "./ModelListField.vue";

const events = { emit: vi.fn(), off: vi.fn(), on: vi.fn() };
const api = { get: vi.fn() };
const panel = { error: vi.fn() };

const initial = {
	columns: {},
	models: [],
	pagination: { page: 1, total: 0 }
};

function factory() {
	return mount(ModelListField, {
		props: {
			endpoints: { field: "pages/test/fields/drafts" },
			initial,
			name: "drafts"
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
		events.emit.mockClear();
		panel.error.mockClear();
	});

	it("announces itself once it is mounted", () => {
		const wrapper = factory();

		expect(events.emit).toHaveBeenCalledTimes(1);
		expect(lastEmit()[0]).toBe("field.loaded");
		expect(lastEmit()[1]).toBe(wrapper.vm);
	});

	it("listens to model updates while mounted", () => {
		const wrapper = factory();

		expect(events.on).toHaveBeenCalledWith("model.update", expect.anything());

		wrapper.unmount();

		expect(events.off).toHaveBeenCalledWith("model.update", expect.anything());
	});

	it("announces itself again after a reload", async () => {
		const state = { ...initial, pagination: { page: 2, total: 25 } };
		api.get.mockResolvedValue(state);

		const wrapper = factory();
		await wrapper.vm.reload({ page: 2 });

		expect(api.get).toHaveBeenCalledWith("pages/test/fields/drafts", {
			page: 2,
			searchterm: null
		});

		// the fresh state replaces the initial one
		expect(wrapper.vm.state).toStrictEqual(state);

		// once on mount, once after the reload
		expect(events.emit).toHaveBeenCalledTimes(2);
		expect(lastEmit()[0]).toBe("field.loaded");
		expect(lastEmit()[1]).toBe(wrapper.vm);
	});

	it("announces itself even when the reload fails", async () => {
		const error = new Error("Nope");
		api.get.mockRejectedValue(error);

		const wrapper = factory();
		await wrapper.vm.reload();

		expect(panel.error).toHaveBeenCalledWith(error);
		expect(wrapper.vm.isProcessing).toBe(false);
		expect(events.emit).toHaveBeenCalledTimes(2);
		expect(lastEmit()[0]).toBe("field.loaded");
		expect(lastEmit()[1]).toBe(wrapper.vm);
	});
});
