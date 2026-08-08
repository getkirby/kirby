import { createApp, type App } from "vue";
import { config } from "@vue/test-utils";
import { vi } from "vitest";
import Panel from "@/panel/panel";
import Helpers from "@/helpers";
import Libraries from "@/libraries";
import SafeHtml from "@/config/safeHtml";

declare global {
	var app: App;
	var panel: Panel;
}

globalThis.app ??= createApp({});

/**
 * The setup runs once per test file, but tests share one module
 * registry (due to `--no-isolate`), so globals below are assigned
 * rather than appended or defaulted: a test file may replace them
 * for its own purposes, and every following file starts clean again.
 */

/**
 * The same plugins the real app installs, so components get
 * `$helper`, `$esc`, `$library` and `v-safe-html` without every
 * test having to mock them. They are self-contained and don't
 * depend on a running Panel.
 */
config.global.plugins = [Helpers, Libraries, SafeHtml];

/**
 * Minimal stand-in for the global panel object,
 * so components can emit deprecation warnings and
 * translate without a full Panel instance.
 */
globalThis.panel = {
	deprecated: vi.fn(),
	t: (key: string) => key
} as unknown as Panel;

/**
 * `$t` is a global property in the real app. It delegates instead of
 * being bound once, so a test that replaces the panel is picked up.
 */
config.global.mocks = {
	$t: (...args: Parameters<Panel["t"]>) => window.panel.t(...args)
};

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
