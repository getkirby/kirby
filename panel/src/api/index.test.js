import { afterEach, describe, expect, it, vi } from "vitest";
import { reactive } from "vue";
import Api from "./index.js";

/**
 * Creates an API module with a minimal panel mock
 */
function factory(csrf = "token-a") {
	const panel = reactive({
		config: {},
		isOffline: false,
		language: { code: "en" },
		system: { csrf: csrf },
		urls: { api: "/api" }
	});

	return {
		api: Api(panel),
		panel: panel
	};
}

describe("api.csrf", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("reads the token from the panel state", () => {
		const { api } = factory();
		expect(api.csrf).toBe("token-a");
	});

	it("follows a token that has been regenerated", () => {
		const { api, panel } = factory();

		// this is what a response with a new `$system` global does
		panel.system.csrf = "token-b";

		expect(api.csrf).toBe("token-b");
	});

	it("sends the current token with each request", async () => {
		const { api, panel } = factory();

		const fetch = vi.spyOn(globalThis, "fetch").mockResolvedValue(
			new Response("{}", {
				headers: { "Content-Type": "application/json" }
			})
		);

		panel.system.csrf = "token-b";

		await api.post("auth/login", { email: "test@getkirby.com" });

		expect(fetch.mock.calls[0][0].headers.get("x-csrf")).toBe("token-b");
	});
});
