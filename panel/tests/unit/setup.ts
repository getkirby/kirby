import { createApp, type App } from "vue";
import { vi } from "vitest";

declare global {
	var app: App;
	// TODO: add proper types for panel global
	var panel: TODO;
}

globalThis.app ??= createApp({});

/**
 * Prevent real HTTP requests during tests.
 * Individual tests that need specific fetch behavior can
 * override this with vi.spyOn or vi.stubGlobal.
 */
vi.stubGlobal(
	"fetch",
	vi
		.fn()
		.mockRejectedValue(new Error("No server available in test environment"))
);
