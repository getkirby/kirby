import { describe, expect, it } from "vitest";
import dayjs from "./dayjs.js";

describe("dayjs.iso()", () => {
  const data = [
    [
      "2020-02-29 16:05:15",
      { year: 2020, month: 1, date: 29, hour: 16, minute: 5, second: 15 }
    ],
    ["2020-02-29", { year: 2020, month: 1, date: 29 }, "date"],
    ["16:05:15", { hour: 16, minute: 5, second: 15 }, "time"]
  ];

  for (const test in data) {
    it(`${data[test][0]}`, () => {
      const dt = dayjs.iso(data[test][0], data[test][2]);

      for (const unit in data[test][1]) {
        expect(dt.get(unit)).toStrictEqual(data[test][1][unit]);
      }
    });
  }
});

describe("dayjs.toISO()", () => {
  const data = [
    [new Date(2020, 6, 3, 17, 24, 11), "2020-07-03 17:24:11"],
    [new Date(2020, 6, 3, 17, 24, 11), "2020-07-03", "date"],
    [new Date(2020, 6, 3, 17, 24, 11), "17:24:11", "time"]
  ];

  for (const test in data) {
    it(`${data[test][1]}`, () => {
      const iso = dayjs(data[test][0]).toISO(data[test][2]);
      expect(iso).toStrictEqual(data[test][1]);
    });
  }
});
