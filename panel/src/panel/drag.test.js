/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Drag from "./drag.js";
import Panel from "./panel.js";

describe.concurrent("panel.drag", () => {
	it("should have a default state", async () => {
		const panel = Panel.create(app);
		const drag = Drag(panel);
		const state = {
			type: null,
			data: {}
		};

		expect(drag.key()).toStrictEqual("drag");
		expect(drag.state()).toStrictEqual(state);
	});
});

describe.concurrent("panel.drag", () => {
	it("should store drag info", async () => {
		const panel = Panel.create(app);
		const drag = Drag(panel);

		expect(drag.isDragging).toStrictEqual(false);
		drag.start("test", { a: "a", b: "b" });
		expect(drag.isDragging).toStrictEqual(true);
		expect(drag.type).toStrictEqual("test");
		expect(drag.data).toStrictEqual({ a: "a", b: "b" });
		drag.stop();
		expect(drag.isDragging).toStrictEqual(false);
	});
});
