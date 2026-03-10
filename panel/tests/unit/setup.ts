import { createApp, type App } from "vue";

declare global {
	var app: App;
}

globalThis.app ??= createApp({});
