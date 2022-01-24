/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dayjs from "./dayjs.js";

describe.concurrent("dayjs.round()", () => {
  const data = {
    "1s: no change": [
      "second",
      1,
      "2020-02-29 16:05:15",
      "2020-02-29 16:05:15"
    ],
    "5s: no change": [
      "second",
      5,
      "2020-02-29 16:05:15",
      "2020-02-29 16:05:15"
    ],
    "5s: floor": ["second", 5, "2020-02-29 16:05:12", "2020-02-29 16:05:10"],
    "5s: ceil": ["second", 5, "2020-02-29 16:05:13", "2020-02-29 16:05:15"],
    "5s: carry": ["second", 5, "2020-02-29 16:59:58", "2020-02-29 17:00:00"],
    "1m: no change": [
      "minute",
      1,
      "2020-02-29 16:05:15",
      "2020-02-29 16:05:00"
    ],
    "1m: ceil sub": ["minute", 1, "2020-02-29 16:05:55", "2020-02-29 16:06:00"],
    "15m: no change": [
      "minute",
      1,
      "2020-02-29 16:07:15",
      "2020-02-29 16:07:00"
    ],
    "15m: floor": ["minute", 15, "2020-02-29 16:07:15", "2020-02-29 16:00:00"],
    "15m: ceil": ["minute", 15, "2020-02-29 16:08:15", "2020-02-29 16:15:00"],
    "15m: ceil sub": [
      "minute",
      15,
      "2020-02-29 16:07:31",
      "2020-02-29 16:15:00"
    ],
    "15m: carry": ["minute", 15, "2020-02-29 23:53:15", "2020-03-01 00:00:00"],
    "1h: no change": ["hour", 1, "2020-02-29 16:05:15", "2020-02-29 16:00:00"],
    "1h: ceil sub": ["hour", 1, "2020-02-29 16:59:15", "2020-02-29 17:00:00"],
    "4h: no change": ["hour", 4, "2020-02-29 16:05:15", "2020-02-29 16:00:00"],
    "4h: floor": ["hour", 4, "2020-02-29 17:00:15", "2020-02-29 16:00:00"],
    "4h: ceil": ["hour", 4, "2020-02-29 15:08:15", "2020-02-29 16:00:00"],
    "4h: ceil sub": ["hour", 4, "2020-02-29 14:07:31", "2020-02-29 16:00:00"],
    "4h: carry": ["hour", 4, "2020-02-29 23:53:15", "2020-03-01 00:00:00"],
    "1D: no change": ["day", 1, "2020-02-29 09:05:15", "2020-02-29 00:00:00"],
    "1D: ceil sub": ["day", 1, "2020-02-29 16:05:15", "2020-03-01 00:00:00"],
    "1M: no change": ["month", 1, "2020-02-14 09:05:15", "2020-02-01 00:00:00"],
    "1M: ceil sub": ["month", 1, "2020-02-29 16:05:15", "2020-03-01 00:00:00"],
    "1Y: no change": ["year", 1, "2020-02-14 09:05:15", "2020-01-01 00:00:00"],
    "1Y: ceil sub": ["year", 1, "2020-09-29 16:05:15", "2021-01-01 00:00:00"]
  };

  for (const test in data) {
    it(test, () => {
      const result = dayjs(data[test][2]).round(data[test][0], data[test][1]);
      expect(result.format("YYYY-MM-DD HH:mm:ss")).toBe(data[test][3]);
    });
  }

  it("Unsupported unit", () => {
    expect(() => {
      dayjs("2020-01-01").round("foo", 1);
    }).toThrow("Invalid rounding unit");
  });

  const sizes = [
    { unit: "second", size: 7 },
    { unit: "minute", size: 7 },
    { unit: "hour", size: 5 },
    { unit: "day", size: 2 },
    { unit: "month", size: 2 },
    { unit: "year", size: 2 }
  ];

  for (const test in sizes) {
    it("Unsupported size: " + sizes[test].unit, () => {
      expect(() => {
        dayjs("2020-01-01").round(sizes[test].unit, sizes[test].size);
      }).toThrow("Invalid rounding size");
    });
  }

  it("Transform 'day' to 'date'", () => {
    const dt = dayjs("2020-01-01");
    expect(dt.round("day", 1)).toEqual(dt.round("date", 1));
  });
});
