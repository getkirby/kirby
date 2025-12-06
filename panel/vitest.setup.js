import { createApp } from "vue";

globalThis.app ??= createApp();

// mock for ResizeObserver
globalThis.ResizeObserver = class ResizeObserver {
	observe() {}
	unobserve() {}
	disconnect() {}
};
