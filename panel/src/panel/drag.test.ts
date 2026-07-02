import { describe, expect, it } from "vitest";
import Drag from "./drag";

describe("panel.drag", () => {
	describe("isDragging", () => {
		it("is false by default", () => {
			const drag = Drag();
			expect(drag.isDragging).toStrictEqual(false);
		});

		it("is true after start()", () => {
			const drag = Drag();
			drag.start("test", {});
			expect(drag.isDragging).toStrictEqual(true);
		});

		it("is false after stop()", () => {
			const drag = Drag();
			drag.start("test", {});
			drag.stop();
			expect(drag.isDragging).toStrictEqual(false);
		});
	});

	describe("start()", () => {
		it("sets type and data", () => {
			const drag = Drag();
			drag.start("text", { a: "a", b: "b" });
			expect(drag.type).toStrictEqual("text");
			expect(drag.data).toStrictEqual({ a: "a", b: "b" });
		});
	});

	describe("stop()", () => {
		it("clears type and data", () => {
			const drag = Drag();
			drag.start("text", { a: "a" });
			drag.stop();
			expect(drag.type).toBeNull();
			expect(drag.data).toStrictEqual({});
		});
	});
});
