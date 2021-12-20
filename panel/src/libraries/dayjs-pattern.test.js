/**
 * @vitest-environment node
 */

import dayjs from "./dayjs.js";

describe("dayjs.pattern.at()", () => {
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

describe("dayjs.pattern.interpret()", () => {
  const data = {
    "YYYY-MM-DD": {
      inputs: ["2020-08-01", "20-8-1", "20-8", "20/8"],
      result: "2020-08-01"
    },
    "MM/DD/YY HH:mm": {
      inputs: ["08/02/15 08:03", "8/2/15 8:3", "8.2.15 8.3"],
      result: "08/02/15 08:03"
    },
    "YY.M.D": {
      inputs: ["20.1.1", "20.1", "20"],
      result: "20.1.1"
    },
    "HH:mm:ss": {
      inputs: ["19:03:22", "19.3.22"],
      result: "19:03:22"
    }
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);

      for (const input of data[test].inputs) {
        const result = pattern.interpret(input).format(test);
        expect(result).toEqual(data[test].result);
      }
    });
  }
});

describe("dayjs.pattern.is12h", () => {
  const data = {
    "YYYY-MM-DD": false,
    "YYYY-MM-DD HH:mm": false,
    "HH:mm": false,
    "hh:mm a": true
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.is12h).toBe(data[test]);
    });
  }
});

describe("dayjs.pattern.isTime", () => {
  const data = {
    "YYYY-MM-DD": false,
    "YYYY-MM-DD HH:mm": false,
    "HH:mm": true,
    "HH:mm:ss": true,
    "hh:mm a": true
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.isTime).toBe(data[test]);
    });
  }
});

describe("dayjs.pattern.parts", () => {
  const data = {
    "YYYY-MM-DD": [
      { index: 0, unit: "year", tokens: ["YY", "YYYY"], start: 0, end: 3 },
      { index: 1, unit: "month", tokens: ["M", "MM"], start: 5, end: 6 },
      { index: 2, unit: "day", tokens: ["D", "DD"], start: 8, end: 9 }
    ],
    "MM/DD/YY HH:mm": [
      { index: 0, unit: "month", tokens: ["M", "MM"], start: 0, end: 1 },
      { index: 1, unit: "day", tokens: ["D", "DD"], start: 3, end: 4 },
      { index: 2, unit: "year", tokens: ["YY", "YYYY"], start: 6, end: 7 },
      { index: 3, unit: "hour", tokens: ["H", "HH"], start: 9, end: 10 },
      { index: 4, unit: "minute", tokens: ["m", "mm"], start: 12, end: 13 }
    ]
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.parts).toEqual(data[test]);
    });
  }
});

describe("dayjs.pattern.separators", () => {
  const data = {
    "YYYY-MM-DD": ["-", "-"],
    "YYYY-MM-DD HH:mm": ["-", "-", " ", ":"],
    "MM/DD/YY h:m a": ["/", "/", " ", ":", " "]
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.separators).toEqual(data[test]);
    });
  }
});

describe("dayjs.pattern.tokens()", () => {
  const h12 = {
    year: ["YY", "YYYY"],
    month: ["M", "MM"],
    day: ["D", "DD"],
    hour: ["h", "hh"],
    minute: ["m", "mm"],
    second: ["s", "ss"],
    meridiem: ["a"]
  };

  for (const test in h12) {
    it(`${test} - 12h`, () => {
      const pattern = dayjs.pattern("YYYY-MM-DD hh:mm a");
      expect(pattern.tokens(test)).toEqual(h12[test]);
    });
  }

  const h24 = {
    ...h12,
    hour: ["H", "HH"],
    meridiem: []
  };

  for (const test in h24) {
    it(`${test} - 24h`, () => {
      const pattern = dayjs.pattern("YYYY-MM-DD HH:mm");
      expect(pattern.tokens(test)).toEqual(h24[test]);
    });
  }
});

describe("dayjs.pattern.unit()", () => {
  const h12 = {
    year: ["YY", "YYYY"],
    month: ["M", "MM"],
    day: ["D", "DD"],
    hour: ["h", "hh"],
    minute: ["m", "mm"],
    second: ["s", "ss"],
    meridiem: ["a"]
  };

  for (const test in h12) {
    it(`${test} - 12h`, () => {
      const pattern = dayjs.pattern("YYYY-MM-DD hh:mm a");

      for (const token of h12[test]) {
        expect(pattern.unit(token)).toBe(test);
      }
    });
  }

  const h24 = {
    ...h12,
    hour: ["H", "HH"],
    meridiem: []
  };

  for (const test in h24) {
    it(`${test} - 24h`, () => {
      const pattern = dayjs.pattern("YYYY-MM-DD HH:mm");

      for (const token of h24[test]) {
        expect(pattern.unit(token)).toBe(test);
      }
    });
  }
});

describe("dayjs.pattern.units", () => {
  const data = {
    "YYYY-MM-DD HH:mm": ["year", "month", "day", "hour", "minute"],
    "MM/DD/YY h:m a": ["month", "day", "year", "hour", "minute", "meridiem"],
    "HH:mm:ss": ["hour", "minute", "second"]
  };

  for (const test in data) {
    it(test, () => {
      const pattern = dayjs.pattern(test);
      expect(pattern.units).toEqual(data[test]);
    });
  }
});

describe("dayjs.pattern.variations()", () => {
  const data = {
    "YYYY-MM-DD": [
      "YYYY-MM-DD",
      "YY-MM-DD",
      "YYYY-M-DD",
      "YY-M-DD",
      "YYYY-MM-D",
      "YY-MM-D",
      "YYYY-M-D",
      "YY-M-D",
      "YYYY-MM",
      "YY-MM",
      "YYYY-M",
      "YY-M",
      "YYYY",
      "YY"
    ],
    "MM/DD/YY HH:mm": [
      "MM/DD/YYYY HH:mm",
      "M/DD/YYYY HH:mm",
      "MM/D/YYYY HH:mm",
      "M/D/YYYY HH:mm",
      "MM/DD/YY HH:mm",
      "M/DD/YY HH:mm",
      "MM/D/YY HH:mm",
      "M/D/YY HH:mm",
      "MM/DD/YYYY H:mm",
      "M/DD/YYYY H:mm",
      "MM/D/YYYY H:mm",
      "M/D/YYYY H:mm",
      "MM/DD/YY H:mm",
      "M/DD/YY H:mm",
      "MM/D/YY H:mm",
      "M/D/YY H:mm",
      "MM/DD/YYYY HH:m",
      "M/DD/YYYY HH:m",
      "MM/D/YYYY HH:m",
      "M/D/YYYY HH:m",
      "MM/DD/YY HH:m",
      "M/DD/YY HH:m",
      "MM/D/YY HH:m",
      "M/D/YY HH:m",
      "MM/DD/YYYY H:m",
      "M/DD/YYYY H:m",
      "MM/D/YYYY H:m",
      "M/D/YYYY H:m",
      "MM/DD/YY H:m",
      "M/DD/YY H:m",
      "MM/D/YY H:m",
      "M/D/YY H:m",
      "MM/DD/YYYY HH",
      "M/DD/YYYY HH",
      "MM/D/YYYY HH",
      "M/D/YYYY HH",
      "MM/DD/YY HH",
      "M/DD/YY HH",
      "MM/D/YY HH",
      "M/D/YY HH",
      "MM/DD/YYYY H",
      "M/DD/YYYY H",
      "MM/D/YYYY H",
      "M/D/YYYY H",
      "MM/DD/YY H",
      "M/DD/YY H",
      "MM/D/YY H",
      "M/D/YY H",
      "MM/DD/YYYY",
      "M/DD/YYYY",
      "MM/D/YYYY",
      "M/D/YYYY",
      "MM/DD/YY",
      "M/DD/YY",
      "MM/D/YY",
      "M/D/YY",
      "MM/DD",
      "M/DD",
      "MM/D",
      "M/D",
      "MM",
      "M"
    ],
    "h:m a": [
      "hh:mm a",
      "h:mm a",
      "hh:m a",
      "h:m a",
      "hh:mm",
      "h:mm",
      "hh:m",
      "h:m",
      "hh",
      "h"
    ]
  };

  for (const test in data) {
    it(test, () => {
      const variations = dayjs.pattern(test).variations();
      expect(variations).toEqual(data[test]);
    });
  }
});
