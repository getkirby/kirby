import { createApp, type App } from "vue";
import { config } from "@vue/test-utils";
import { vi } from "vitest";

declare global {
	var app: App;
	// TODO: add proper types for panel global
	var panel: TODO;
}

globalThis.app ??= createApp({});

/**
 * The panel's `k-*` UI components are globally registered
 * in the real app but  not in unit tests. Instead of registering
 * or stubbing every one of them, we let Vue render any unresolved
 * `k-*` component as its literal tag (preserving the attributes and
 * slots the component tests assert against) and silence the resulting
 * "Failed to resolve component" warnings. All other Vue warnings are
 * kept so real problems stay visible.
 */
config.global.config.warnHandler = (msg, instance, trace) => {
	if (msg.includes("Failed to resolve component: k-")) {
		return;
	}

	console.warn(msg + trace);
};

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
