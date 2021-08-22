
import Datetime from "./datetime";

import dayjs from "dayjs";
import customParseFormat from "dayjs/plugin/customParseFormat";
import utc from "dayjs/plugin/utc";
dayjs.extend(customParseFormat);
dayjs.extend(utc);

describe("Datetime.format", () => {
  it("no value", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.format()).to.equal(null);
  });

  it("invalid value", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.format("aaaa-bb-cc")).to.equal(null);
  });

  it("valid string value", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.format("2020-03-01")).to.equal("2020-03-01");
  });

  it("valid dayjs object", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    const value = dayjs("2020-03-01")
    expect(dt.format(value)).to.equal("2020-03-01");
  });

  it("custom format", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    const value = dayjs("2020-03-01")
    expect(dt.format(value, "MM/DD/YY")).to.equal("03/01/20");
  });

  it("time format", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD", { unit: "second", size: 5});
    const value = dayjs("16:03:7", "HH:mm:ss")
    expect(dt.format(value, "HH:mm:ss")).to.equal("16:03:05");
  });
});

describe("Datetime.iso", () => {
  it("invalid value", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.iso("aaa")).to.equal("");
  });

  it("full iso", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.iso("2020-03-01")).to.equal("2020-03-01 00:00:00");
  });

  it("time only", () => {
    const dt = new Datetime(dayjs, "HH:mm:ss", { unit: "second" });
    expect(dt.iso("15:03:12")).to.equal("15:03:12");
  });
});

describe("Datetime.nearest: round to nearest step", () => {

  it("step: 10 seconds", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm:ss", {"unit": "second", "size": 10});
    let result;

    result = dt.nearest(dayjs("2021-08-18 00:00:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 00:00:00");

    result = dt.nearest(dayjs("2021-08-18 19:27:13"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 19:27:10");

    result = dt.nearest(dayjs("2021-08-18 10:59:59"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 11:00:00");
  });

  it("step: 30 seconds", () => {
    const dt = new Datetime(dayjs, "HH:mm:ss", {"unit": "second", "size": 30});
    let result;

    result = dt.nearest(dayjs("2000-01-11 22:35:15"));
    expect(result.format("HH:mm:ss")).to.equal("22:35:30");

    result = dt.nearest(dayjs("2000-01-11 22:35:30"));
    expect(result.format("HH:mm:ss")).to.equal("22:35:30");

    result = dt.nearest(dayjs("2000-01-11 22:35:45"));
    expect(result.format("HH:mm:ss")).to.equal("22:36:00");
  });

  it("step: 5 minutes", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm:ss", {"unit": "minute", "size": 5});
    let result;

    result = dt.nearest(dayjs("2021-08-18 00:00:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 00:00:00");

    result = dt.nearest(dayjs("2021-08-18 19:27:15"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 19:25:00");

    result = dt.nearest(dayjs("2021-08-18 10:59:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 11:00:00");
  });

  it("step: 2 hours", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm:ss", {"unit": "hour", "size": 2});
    let result;

    result = dt.nearest(dayjs("2021-08-18 00:00:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 00:00:00");

    result = dt.nearest(dayjs("2021-08-18 19:27:15"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 20:00:00");

    result = dt.nearest(dayjs("2021-08-18 23:59:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-19 00:00:00");
  });

  it("step: 1 day", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm:ss");
    let result;

    result = dt.nearest(dayjs("2021-08-17 00:00:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-17 00:00:00");

    result = dt.nearest(dayjs("2021-08-17 19:27:15"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-18 00:00:00");

    result = dt.nearest(dayjs("2021-08-31 23:59:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-09-01 00:00:00");
  });

  it("step: 1 month", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm:ss", {"unit": "month"});
    let result;

    result = dt.nearest(dayjs("2021-08-17 00:00:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-09-01 00:00:00");

    result = dt.nearest(dayjs("2021-08-13 19:27:15"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-08-01 00:00:00");

    result = dt.nearest(dayjs("2021-08-31 23:59:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-09-01 00:00:00");

    result = dt.nearest(dayjs("2021-12-31 23:59:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2022-01-01 00:00:00");
  });

  it("step: 1 year", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm:ss", {"unit": "year"});
    let result;

    result = dt.nearest(dayjs("2021-05-17 00:00:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2021-01-01 00:00:00");

    result = dt.nearest(dayjs("2021-09-31 23:59:00"));
    expect(result.format("YYYY-MM-DD HH:mm:ss")).to.equal("2022-01-01 00:00:00");
  });

  it("YYYY-MM-DD by 5 days", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD", { size: 5 });
    let result;

    result = dt.nearest(dayjs("2020-03-07"));
    expect(result.format("YYYY-MM-DD")).to.equal("2020-03-06");
  });

  it("YYYY-MM-DD HH:mm by 1 day (default)", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm");
    let result;

    result = dt.parseToNearest("2020-03-16 15:10");
    expect(result.format("YYYY-MM-DD HH:mm")).to.equal("2020-03-17 00:00");
  });

  it("YYYY-MM-DD by 5 days", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD", { unit: "day", size: 5 });
    let result;

    result = dt.nearest(dayjs("2020-03-07"));
    expect(result.format("YYYY-MM-DD")).to.equal("2020-03-06");
  });

  it("HH:mm by 15 minutes", () => {
    const dt = new Datetime(dayjs, "HH:mm", { unit: "minute", size: 15 });
    let result;

    result = dt.parseToNearest("12:01");
    expect(result.format("HH:mm")).to.equal("12:00");

    result = dt.parseToNearest("12:25");
    expect(result.format("HH:mm")).to.equal("12:30");
  });
});

// describe("Datetime.manipulate", () => {
//   it("no value", () => {
//     const dt = new Datetime(dayjs, "YYYY-MM-DD");
//     expect(dt.manipulate("")).to.deep.equal(dt.nearest(dayjs()));
//   });

//   it("no specific segment", () => {
//     const dt = new Datetime(dayjs, "YYYY-MM-DD");
//     let result;

//     result = dt.manipulate("2020-07-03", null, "add");
//     expect(result.format("YYYY-MM-DD")).to.equal("2020-07-04");

//     result = dt.manipulate("2020-07-03", null, "subtract");
//     expect(result.format("YYYY-MM-DD")).to.equal("2020-07-02");
//   });

//   it("with segment", () => {
//     const dt = new Datetime(dayjs, "YYYY-MM-DD");
//     let result;

//     result = dt.manipulate("2020-07-03", "month", "add");
//     expect(result.format("YYYY-MM-DD")).to.equal("2020-08-03");

//     result = dt.manipulate("2020-07-03", "month", "subtract");
//     expect(result.format("YYYY-MM-DD")).to.equal("2020-06-03");

//     result = dt.manipulate("2020-07-03", "year", "add");
//     expect(result.format("YYYY-MM-DD")).to.equal("2021-07-03");

//     result = dt.manipulate("2020-07-03", "year", "subtract");
//     expect(result.format("YYYY-MM-DD")).to.equal("2019-07-03");
//   });

//   it("with segment unit different than step unit", () => {
//     const dt = new Datetime(dayjs, "YYYY-MM-DD HH:mm", { unit: "minute", size: 5 });
//     let result;

//     result = dt.manipulate("2020-07-03 15:03", "day", "add");
//     expect(result.format("YYYY-MM-DD HH:mm")).to.equal("2020-07-04 15:05");

//     result = dt.manipulate("2020-07-03 15:03", "minute", "add");
//     expect(result.format("YYYY-MM-DD HH:mm")).to.equal("2020-07-03 15:10");
//   });

//   it("with meridiem unit", () => {
//     const dt = new Datetime(dayjs, "YYYY-MM-DD hh:mm a", { unit: "minute" });
//     let result;

//     result = dt.manipulate("2020-07-03 3:03 am", "meridiem", "add");
//     expect(result.format("YYYY-MM-DD hh:mm a")).to.equal("2020-07-03 03:03 pm");
//     result = dt.manipulate("2020-07-03 3:03 am", "meridiem", "subtract");
//     expect(result.format("YYYY-MM-DD hh:mm a")).to.equal("2020-07-03 03:03 pm");

//     result = dt.manipulate("2020-07-03 3:03 pm", "meridiem", "add");
//     expect(result.format("YYYY-MM-DD hh:mm a")).to.equal("2020-07-03 03:03 am");
//     result = dt.manipulate("2020-07-03 3:03 pm", "meridiem", "subtract");
//     expect(result.format("YYYY-MM-DD hh:mm a")).to.equal("2020-07-03 03:03 am");
//   });
// });

describe("Datetime.parse: parses date", () => {
  it("YYYY-MM-DD", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    let result;

    // exact format, all parts
    result = dt.parse("2020-08-01");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2020);
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(1);

    // fuzzy format, all parts
    result = dt.parse("20-8-1");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2020);
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(1);

    // exact format, some parts
    result = dt.parse("2020");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2020);
    expect(result.month()).to.equal(0);
    expect(result.date()).to.equal(1);

    // fuzzy format, some parts
    result = dt.parse("20");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2020);
    expect(result.month()).to.equal(0);
    expect(result.date()).to.equal(1);

    // fuzzy format, different separators
    result = dt.parse("20/8/1");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2020);
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(1);
  });

  it("MM/DD/YY HH:mm", () => {
    const dt = new Datetime(dayjs, "MM/DD/YY HH:mm");
    let result;

    // exact format, all parts
    result = dt.parse("08/15/15 19:03");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2015);
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(15);
    expect(result.hour()).to.equal(19);
    expect(result.minute()).to.equal(3);

    // fuzzy format, all parts
    result = dt.parse("8/2/15 8:3");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2015);
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(2);
    expect(result.hour()).to.equal(8);
    expect(result.minute()).to.equal(3);

    // exact format, some parts
    result = dt.parse("08/02");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(dayjs().year());
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(2);
    expect(result.hour()).to.equal(0);
    expect(result.minute()).to.equal(0);

    // fuzzy format, some parts
    result = dt.parse("8/2");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(dayjs().year());
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(2);
    expect(result.hour()).to.equal(0);
    expect(result.minute()).to.equal(0);

    // fuzzy format, different separators
    result = dt.parse("8-2-15 8.3");
    expect(result.isValid()).to.equal(true);
    expect(result.year()).to.equal(2015);
    expect(result.month()).to.equal(7);
    expect(result.date()).to.equal(2);
    expect(result.hour()).to.equal(8);
    expect(result.minute()).to.equal(3);
  });

  it("HH:mm:ss", () => {
    const dt = new Datetime(dayjs, "HH:mm:ss");
    let result;

    // exact format, all parts
    result = dt.parse("19:03:22");
    expect(result.hour()).to.equal(19);
    expect(result.minute()).to.equal(3);
    expect(result.second()).to.equal(22);

    // fuzzy format, all parts
    result = dt.parse("8:3:7");
    expect(result.hour()).to.equal(8);
    expect(result.minute()).to.equal(3);
    expect(result.second()).to.equal(7);

    // exact format, some parts
    result = dt.parse("19:03");
    expect(result.hour()).to.equal(19);
    expect(result.minute()).to.equal(3);
    expect(result.second()).to.equal(0);

    // fuzzy format, some parts
    result = dt.parse("7:3");
    expect(result.hour()).to.equal(7);
    expect(result.minute()).to.equal(3);
    expect(result.second()).to.equal(0);

    // fuzzy format, different separators
    result = dt.parse("7/3-2");
    expect(result.hour()).to.equal(7);
    expect(result.minute()).to.equal(3);
    expect(result.second()).to.equal(2);
  });

  it("hh:mm a", () => {
    const dt = new Datetime(dayjs, "hh:mm a");
    let result;

    // exact format, all parts
    result = dt.parse("07:03 pm");
    expect(result.hour()).to.equal(19);
    expect(result.minute()).to.equal(3);

    // fuzzy format, all parts
    result = dt.parse("7:3 am");
    expect(result.hour()).to.equal(7);
    expect(result.minute()).to.equal(3);

    // exact format, some parts
    result = dt.parse("19:03");
    expect(result.hour()).to.equal(19);
    expect(result.minute()).to.equal(3);

    // fuzzy format, some parts
    result = dt.parse("7:3");
    expect(result.hour()).to.equal(7);
    expect(result.minute()).to.equal(3);

    result = dt.parse("7");
    expect(result.hour()).to.equal(7);
    expect(result.minute()).to.equal(0);

    // fuzzy format, different separators
    result = dt.parse("7/3-pm");
    expect(result.hour()).to.equal(19);
    expect(result.minute()).to.equal(3);
  });

  it("empty values", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.parse(null)).to.equal(null);
    expect(dt.parse("")).to.equal(null);
    expect(dt.parseToNearest(null)).to.equal(null);
    expect(dt.parseToNearest("")).to.equal(null);
  });
});

describe("Datetime.validate", () => {
  it("no value, no limit", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    expect(dt.validate("")).to.equal(false);
  });

  it("no value, but limit", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    // @todo this seems weird
    expect(dt.validate("", "2015-01-05")).to.equal(true);
  });

  it("min by day", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    let result;

    result = dt.validate("2020-01-05", "2020-01-05", "isAfter");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-06", "2020-01-05", "isAfter");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-04", "2020-01-05", "isAfter");
    expect(result).to.equal(false);
  });

  it("min by month", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    let result;

    result = dt.validate("2020-01-05", "2020-01-05", "isAfter", "month");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-06", "2020-01-05", "isAfter", "month");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-04", "2020-01-05", "isAfter", "month");
    expect(result).to.equal(true);
    result = dt.validate("2019-12-12", "2020-01-05", "isAfter", "month");
    expect(result).to.equal(false);
  });

  it("max by day", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    let result;

    result = dt.validate("2020-01-05", "2020-01-05", "isBefore");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-06", "2020-01-05", "isBefore");
    expect(result).to.equal(false);
    result = dt.validate("2020-01-04", "2020-01-05", "isBefore");
    expect(result).to.equal(true);
  });

  it("max by month", () => {
    const dt = new Datetime(dayjs, "YYYY-MM-DD");
    let result;

    result = dt.validate("2020-01-05", "2020-01-05", "isBefore", "month");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-06", "2020-01-05", "isBefore", "month");
    expect(result).to.equal(true);
    result = dt.validate("2020-01-04", "2020-01-05", "isBefore", "month");
    expect(result).to.equal(true);
    result = dt.validate("2020-02-12", "2020-01-05", "isBefore", "month");
    expect(result).to.equal(false);
  });

  it("time-only", () => {
    const dt = new Datetime(dayjs, "HH:mm");
    let result;

    result = dt.validate("15:05:00", "15:05:00", "isBefore", "second");
    expect(result).to.equal(true);
    result = dt.validate("15:00:00", "15:05:00", "isBefore", "second");
    expect(result).to.equal(true);
    result = dt.validate("15:10:00", "15:05:00", "isBefore", "second");
    expect(result).to.equal(false);
  });
});