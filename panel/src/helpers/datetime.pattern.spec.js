
import { Pattern } from "./datetime";

describe("parses pattern", () => {
  it("YYYY-MM-DD", () => {
    const pattern = new Pattern("YYYY-MM-DD");
    expect(pattern.parts).to.deep.equal(["YYYY", "MM", "DD"]);
    expect(pattern.separators).to.deep.equal(["-", "-"]);
  });

  it("MM/DD/YY HH:mm", () => {
    const pattern = new Pattern("MM/DD/YY HH:mm");
    expect(pattern.parts).to.deep.equal(["MM", "DD", "YY", "HH", "mm"]);
    expect(pattern.separators).to.deep.equal(["/", "/", " ", ":"]);
  });
});

describe("gets related tokens", () => {
  it("token()", () => {
    const pattern = new Pattern("YYYY-MM-DD");
    expect(pattern.token("YY")).to.deep.equal(["YY", "YYYY"]);
    expect(pattern.token("M")).to.deep.equal(["M", "MM"]);
    expect(pattern.token("DD")).to.deep.equal(["D", "DD"]);
  });
});

describe("gets unit from token", () => {
  it("unit()", () => {
    const pattern = new Pattern("YYYY-MM-DD hh:mm a");
    expect(pattern.unit("YY")).to.deep.equal("year");
    expect(pattern.unit("M")).to.deep.equal("month");
    expect(pattern.unit("DD")).to.deep.equal("day");
    expect(pattern.unit("hh")).to.deep.equal("hour");
    expect(pattern.unit("m")).to.deep.equal("minute");
    expect(pattern.unit("s")).to.deep.equal("second");
    expect(pattern.unit("a")).to.deep.equal("meridiem");
  });
});

describe("gets all units", () => {
  it("units()", () => {
    const pattern = new Pattern("YYYY-MM-DD HH:mm");
    expect(pattern.units()).to.deep.equal(["year", "month", "day", "hour", "minute"]);
  });
});

describe("checks if is 12/24 h time", () => {
  it("YYYY-MM-DD", () => {
    const pattern = new Pattern("YYYY-MM-DD");
    expect(pattern.is12h).to.deep.equal(false);
  });
  it("YYYY-MM-DD HH:mm", () => {
    const pattern = new Pattern("YYYY-MM-DD HH:mm");
    expect(pattern.is12h).to.deep.equal(false);
  });
  it("HH:mm", () => {
    const pattern = new Pattern("HH:mm");
    expect(pattern.is12h).to.deep.equal(false);
  });
  it("hh:mm a", () => {
    const pattern = new Pattern("hh:mm a");
    expect(pattern.is12h).to.deep.equal(true);
  });
});

describe("checks if is time-only", () => {
  it("YYYY-MM-DD", () => {
    const pattern = new Pattern("YYYY-MM-DD");
    expect(pattern.isTimeOnly()).to.equal(false);
  });
  it("YYYY-MM-DD HH:mm", () => {
    const pattern = new Pattern("YYYY-MM-DD HH:mm");
    expect(pattern.isTimeOnly()).to.equal(false);
  });
  it("HH:mm", () => {
    const pattern = new Pattern("HH:mm");
    expect(pattern.isTimeOnly()).to.equal(true);
  });
  it("HH:mm:ss", () => {
    const pattern = new Pattern("HH:mm:ss");
    expect(pattern.isTimeOnly()).to.equal(true);
  });
  it("hh:mm a", () => {
    const pattern = new Pattern("hh:mm a");
    expect(pattern.isTimeOnly()).to.equal(true);
  });
});

describe("creates pattern variants", () => {
  it("YYYY-MM-DD", () => {
    const pattern = new Pattern("YYYY-MM-DD");
    expect(pattern.variants()).to.deep.equal([
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
      "YY",
    ]);
  });

  it("MM/DD/YY HH:mm", () => {
    const pattern = new Pattern("MM/DD/YY HH:mm");
    expect(pattern.variants()).to.deep.equal([
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
      "M",
    ]);
  });

  it("h:m", () => {
    const pattern = new Pattern("h:m a");
    expect(pattern.variants()).to.deep.equal([
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
    ]);
  });
});
