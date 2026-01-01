import Vue from "vue";

Vue.config.productionTip = false;
Vue.config.devtools = false;

// mock for ResizeObserver
globalThis.ResizeObserver = class ResizeObserver {
	observe() {}
	unobserve() {}
	disconnect() {}
};
