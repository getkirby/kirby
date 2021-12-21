import debounce from "./debounce.js";

describe("$helper.debounce()", () => {
  let clock = null;

  beforeEach(() => {
    clock = sinon.useFakeTimers();
  });

  afterEach(() => {
    clock.restore();
  });

  it("calls callback after 100ms", () => {
    const callback = sinon.fake();
    const throttled = debounce(callback, 100);

    throttled();

    clock.tick(99);
    expect(callback.notCalled).toBe(true);

    clock.tick(1);
    expect(callback.calledOnce).toBe(true);
  });
});
