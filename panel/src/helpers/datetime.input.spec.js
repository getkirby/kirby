
import { Input } from "./datetime";

describe("parses segments", () => {
  it("YYYY-MM-DD", () => {
    const dom = document.createElement("input");
    const input = new Input(dom, "YYYY-MM-DD");

    expect(input.segments[0].index).to.equal(0);
    expect(input.segments[0].value).to.equal("YYYY");
    expect(input.segments[0].unit).to.equal("year");
    expect(input.segments[0].start).to.equal(0);
    expect(input.segments[0].end).to.equal(3);

    expect(input.segments[1].index).to.equal(1);
    expect(input.segments[1].value).to.equal("MM");
    expect(input.segments[1].unit).to.equal("month");
    expect(input.segments[1].start).to.equal(5);
    expect(input.segments[1].end).to.equal(6);

    expect(input.segments[2].index).to.equal(2);
    expect(input.segments[2].value).to.equal("DD");
    expect(input.segments[2].unit).to.equal("day");
    expect(input.segments[2].start).to.equal(8);
    expect(input.segments[2].end).to.equal(9);
  });

  it("MM/DD/YY HH:mm", () => {
    const dom = document.createElement("input");
    const input = new Input(dom, "MM/DD/YY HH:mm");

    expect(input.segments[0].index).to.equal(0);
    expect(input.segments[0].value).to.equal("MM");
    expect(input.segments[0].unit).to.equal("month");
    expect(input.segments[0].start).to.equal(0);
    expect(input.segments[0].end).to.equal(1);

    expect(input.segments[1].index).to.equal(1);
    expect(input.segments[1].value).to.equal("DD");
    expect(input.segments[1].unit).to.equal("day");
    expect(input.segments[1].start).to.equal(3);
    expect(input.segments[1].end).to.equal(4);

    expect(input.segments[2].index).to.equal(2);
    expect(input.segments[2].value).to.equal("YY");
    expect(input.segments[2].unit).to.equal("year");
    expect(input.segments[2].start).to.equal(6);
    expect(input.segments[2].end).to.equal(7);

    expect(input.segments[3].index).to.equal(3);
    expect(input.segments[3].value).to.equal("HH");
    expect(input.segments[3].unit).to.equal("hour");
    expect(input.segments[3].start).to.equal(9);
    expect(input.segments[3].end).to.equal(10);

    expect(input.segments[4].index).to.equal(4);
    expect(input.segments[4].value).to.equal("mm");
    expect(input.segments[4].unit).to.equal("minute");
    expect(input.segments[4].start).to.equal(12);
    expect(input.segments[4].end).to.equal(13);
  });
});

