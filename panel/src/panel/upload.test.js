import { describe, expect, it, vi } from "vitest";
import Upload from "./upload.js";

describe("panel.upload", () => {
	it("should limit the number of parallel uploads", async () => {
		let active = 0;
		let max = 0;

		const panel = {
			config: { upload: 1000, uploads: 3 },
			dialog: { close: vi.fn() },
			events: { emit: vi.fn() },
			notification: { success: vi.fn() },
			system: { csrf: "csrf" }
		};

		const upload = Upload(panel);
		upload.url = "/api/pages/test/files";
		upload.files = Array.from({ length: 8 }, (_, index) => ({
			completed: false,
			extension: "jpg",
			name: "image-" + index
		}));

		upload.upload = vi.fn(async (file) => {
			active++;
			max = Math.max(max, active);
			await new Promise((resolve) => setTimeout(resolve, 5));
			active--;
			file.completed = true;
		});

		await upload.submit();

		expect(upload.upload).toHaveBeenCalledTimes(8);
		expect(max).toBe(3);
	});
});
