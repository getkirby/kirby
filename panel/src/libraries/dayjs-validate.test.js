/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dayjs from "./dayjs.js";

describe.concurrent("dayjs.validate()", () => {
  const data = {
    "min by day": [
      {
        boundary: "2020-01-05",
        type: "min",
        inputs: {
          "2020-01-05": true,
          "2020-01-06": true,
          "2020-01-04": false
        }
      }
    ],
    "max by day": [
      {
        boundary: "2020-01-05",
        type: "max",
        inputs: {
          "2020-01-05": true,
          "2020-01-06": false,
          "2020-01-04": true
        }
      }
    ],
    "min by month": [
      {
        boundary: "2020-01-05",
        type: "min",
        unit: "month",
        inputs: {
          "2020-01-05": true,
          "2020-01-06": true,
          "2020-01-04": true,
          "2019-12-12": false
        }
      }
    ],
    "max by month": [
      {
        boundary: "2020-01-05",
        type: "max",
        unit: "month",
        inputs: {
          "2020-01-05": true,
          "2020-01-06": true,
          "2020-01-04": true,
          "2020-02-12": false
        }
      }
    ],
    "time-only": [
      {
        boundary: "15:05:00",
        type: "max",
        unit: "second",
        inputs: {
          "15:05:00": true,
          "15:00:00": true,
          "15:10:00": false
        }
      }
    ]
  };

  for (const test in data) {
    it(test, () => {
      for (const input in data[test].inputs) {
        const result = dayjs(input).validate(
          data[test].boundary,
          data[test].type,
          data[test].unit
        );
        expect(result).toBe(data[test][input]);
      }
    });
  }

  it("no parameters", () => {
    expect(dayjs().validate()).toBe(true);
    expect(dayjs("Invalid").validate()).toBe(false);
  });

  it("invalid dayjs object", () => {
    expect(dayjs("Invalid").validate("2020-01-01")).toBe(false);
  });
});
