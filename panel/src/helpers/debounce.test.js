import { vi } from "vitest";
import debounce from "./debounce.js";

describe("$helper.debounce()", () => {
  beforeEach(() => {
    vi.useFakeTimers();
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  it("calls callback after 100ms", () => {
    const callback = vi.fn(() => {});
    const throttled = debounce(callback, 100);

    throttled();

    vi.advanceTimersByTime(99);
    expect(callback).not.toHaveBeenCalled();

    vi.advanceTimersByTime(1);
    expect(callback).toHaveBeenCalledTimes(1);
  });
});
