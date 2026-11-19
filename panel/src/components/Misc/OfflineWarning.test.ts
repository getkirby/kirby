import { describe, expect, it } from "@test/unit";
import { mount as vueMount } from "@vue/test-utils";
import { reactive } from "vue";
import OfflineWarning from "./OfflineWarning.vue";

function panel(isOffline = true, isLocal = false) {
	return reactive({ isOffline, system: { isLocal } });
}

function mount(attrs = {}, $panel = panel()) {
	return vueMount(OfflineWarning, {
		attrs,
		global: { mocks: { $panel } }
	});
}

describe("k-offline-warning", () => {
	// $el
	describe("element", () => {
		it.rendersAs(mount, "DIV", "k-offline-warning");
		it.acceptsClass(mount);
		it.acceptsStyle(mount);

		it("warns about being offline", () => {
			const wrapper = mount();
			expect(wrapper.find("k-icon").attributes("type")).toBe("bolt");
			expect(wrapper.find("p").text()).toBe("error.offline");
		});

		it("renders nothing while online", () => {
			expect(mount({}, panel(false)).html()).toBe("<!--v-if-->");
		});

		it("renders nothing on a local system", () => {
			expect(mount({}, panel(true, true)).html()).toBe("<!--v-if-->");
		});

		it("appears when the connection drops", async () => {
			const $panel = panel(false);
			const wrapper = mount({}, $panel);
			$panel.isOffline = true;
			await wrapper.vm.$nextTick();
			expect(wrapper.find(".k-offline-warning").exists()).toBe(true);
		});
	});
});
