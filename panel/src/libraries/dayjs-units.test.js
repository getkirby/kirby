/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dayjs from "./dayjs.js";

const data = {
  "12h": { state: true, hour: ["h", "hh"], meridiem: ["a"] },
  "24h": { state: false, hour: ["H", "HH"], meridiem: [] }
};

describe.concurrent("dayjs.units", () => {
  for (const test in data) {
    it(test, () => {
      const map = dayjs.units(data[test].state);
      expect(map.hour).toEqual(data[test].hour);
      expect(map.meridiem).toEqual(data[test].meridiem);
    });
  }
});
