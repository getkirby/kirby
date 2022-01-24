/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dayjs from "./dayjs.js";

describe.concurrent("dayjs.merge()", () => {
  const data = [
    [
      "2020-02-29 16:05:15",
      "2021-03-01 18:42:11",
      "date",
      "2021-03-01 16:05:15"
    ],
    [
      "2020-02-29 16:05:15",
      "2020-03-01 18:42:11",
      "time",
      "2020-02-29 18:42:11"
    ],
    [
      "2020-02-29 16:05:15",
      "2021-03-01 18:42:11",
      ["year", "date", "minute"],
      "2021-02-01 16:42:15"
    ]
  ];

  for (const test in data) {
    it(`${data[test][2]}: ${data[test][0]} <- ${
      Array.isArray(data[test][1]) ? data[test][1].join(",") : data[test][1]
    }`, () => {
      const a = dayjs(data[test][0]);
      const b = dayjs(data[test][1]);
      expect(a.merge(b, data[test][2])).toEqual(dayjs(data[test][3]));
    });
  }

  it("Invalid input", () => {
    const a = dayjs("2020-01-01");
    expect(a.merge(undefined)).toStrictEqual(a);
    expect(a.merge(null)).toStrictEqual(a);
    expect(a.merge(dayjs("Invalid"))).toStrictEqual(a);
  });

  it("Unsupported unit alias", () => {
    const a = dayjs("2020-01-01");
    const b = dayjs("2020-02-01");
    expect(() => {
      a.merge(b, "foo");
    }).toThrow("Invalid merge unit alias");
  });
});
