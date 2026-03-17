import { createApp, type App } from "vue";

declare global {
	var app: App;
	// TODO: add proper types for panel global
	var panel: TODO;
}

globalThis.app ??= createApp({});
