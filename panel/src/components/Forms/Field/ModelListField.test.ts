import { beforeEach, describe, expect, it, vi } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import ModelListField from "./ModelListField.vue";

const events = { emit: vi.fn(), off: vi.fn(), on: vi.fn() };
const api = { get: vi.fn() };
const panel = { error: vi.fn() };

const initial = {
	columns: {},
	models: [],
	pagination: { page: 1, total: 0 }
};

function mount(props = {}, attrs = {}) {
	return vueMount(ModelListField, {
		props: {
			endpoints: { field: "pages/test/fields/drafts" },
			initial,
			name: "drafts",
			...props
		},
		attrs,
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

	// $el
	describe("element", () => {
		it.rendersAs(mount, "K-FIELD", "k-modellist-field");
		it.inheritsNoAttrs(mount);
	});

	// methods
	describe("reload()", () => {
		it("replaces the initial state with a fresh one", async () => {
			const state = { ...initial, pagination: { page: 2, total: 25 } };
			api.get.mockResolvedValue(state);

			const wrapper = mount();
			await wrapper.vm.reload({ page: 2 });

			expect(api.get).toHaveBeenCalledWith("pages/test/fields/drafts", {
				page: 2,
				searchterm: null
			});
			expect(wrapper.vm.state).toStrictEqual(state);
		});

		it("reports a failing request", async () => {
			const error = new Error("Nope");
			api.get.mockRejectedValue(error);

			const wrapper = mount();
			await wrapper.vm.reload();

			expect(panel.error).toHaveBeenCalledWith(error);
			expect(wrapper.vm.isProcessing).toBe(false);
		});
	});

	// events
	describe("field.loaded event", () => {
		it("is emitted once mounted", () => {
			const wrapper = mount();

			expect(events.emit).toHaveBeenCalledTimes(1);
			expect(lastEmit()[0]).toBe("field.loaded");
			expect(lastEmit()[1]).toBe(wrapper.vm);
		});

		it("is emitted again after a reload", async () => {
			api.get.mockResolvedValue(initial);

			const wrapper = mount();
			await wrapper.vm.reload();

			// once on mount, once after the reload
			expect(events.emit).toHaveBeenCalledTimes(2);
			expect(lastEmit()[0]).toBe("field.loaded");
			expect(lastEmit()[1]).toBe(wrapper.vm);
		});

		it("is emitted even when the reload fails", async () => {
			api.get.mockRejectedValue(new Error("Nope"));

			const wrapper = mount();
			await wrapper.vm.reload();

			expect(events.emit).toHaveBeenCalledTimes(2);
			expect(lastEmit()[0]).toBe("field.loaded");
			expect(lastEmit()[1]).toBe(wrapper.vm);
		});
	});

	describe("model.update event", () => {
		it("is listened to while mounted", () => {
			const wrapper = mount();

			expect(events.on).toHaveBeenCalledWith("model.update", expect.anything());

			wrapper.unmount();

			expect(events.off).toHaveBeenCalledWith(
				"model.update",
				expect.anything()
			);
		});
	});
});
