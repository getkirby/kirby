/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dayjs from "./dayjs.js";

describe.concurrent("dayjs.pattern.at()", () => {
  const data = {
    "YYYY-MM-DD": [
      { start: 0, unit: "year" },
      { start: 2, unit: "year" },
      { start: 5, unit: "month" },
      { start: 6, unit: "month" },
      { start: 9, unit: "day" },
      { start: 8, end: 10, unit: "day" },
      { start: 6, end: 10, unit: "month" },
      { start: 0, end: 4, unit: "year" }
    ],
    "MM/DD/YY HH:mm": [
      { start: 0, unit: "month" },
      { start: 1, unit: "month" },
      { start: 3, unit: "day" },
      { start: 4, unit: "day" },
      { start: 6, unit: "year" },
      { start: 10, unit: "hour" },
      { start: 9, end: 11, unit: "hour" }
    ]
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);

      for (const cursor of data[test]) {
        const part = pattern.at(cursor.start, cursor.end);
        expect(part.unit).toBe(cursor.unit);
      }
    });
  }
});

describe("dayjs.pattern.format()", () => {
  it("no value", () => {
    const pattern = dayjs.pattern("YYYY-MM-DD");
    expect(pattern.format()).toBe(null);
  });

  it("invalid value", () => {
    const pattern = dayjs.pattern("YYYY-MM-DD");
    expect(pattern.format(dayjs("aaaa-bb-cc"))).toBe(null);
  });

  const dt = dayjs("2020-05-04 13:14:03");

  const data = {
    "YYYY-MM-DD": "2020-05-04",
    "M/D/YY h:m a": "5/4/20 1:14 pm",
    "H:m:s": "13:14:3"
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.format(dt)).toBe(data[test]);
    });
  }
});

describe("dayjs.pattern.parts", () => {
  const data = {
    "YYYY-MM-DD": [
      { index: 0, unit: "year", start: 0, end: 3 },
      {
        index: 1,
        unit: "month",
        start: 5,
        end: 6
      },
      { index: 2, unit: "day", start: 8, end: 9 }
    ],
    "MM/DD/YY HH:mm": [
      {
        index: 0,
        unit: "month",
        start: 0,
        end: 1
      },
      { index: 1, unit: "day", start: 3, end: 4 },
      { index: 2, unit: "year", start: 6, end: 7 },
      {
        index: 3,
        unit: "hour",
        start: 9,
        end: 10
      },
      { index: 4, unit: "minute", start: 12, end: 13 }
    ]
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.parts).toEqual(data[test]);
    });
  }
});
