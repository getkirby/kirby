/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Fiber from "./index.js";

describe.concurrent("$fiber", () => {
  it("should convert array to string", () => {
    const fiber = new Fiber();

    expect(fiber.arrayToString("foo")).toBe("foo");
    expect(fiber.arrayToString(["a", "b"])).toBe("a,b");
  });

  it("should build the body", () => {
    const fiber = new Fiber();

    expect(fiber.body("foo")).toBe("foo");
    expect(fiber.body({ a: "b" })).toBe(JSON.stringify({ a: "b" }));
  });

  it("should send the $view request", async () => {
    let fiber = new Fiber({
      base: "https://getkirby.com/"
    });

    // fetch mock
    fiber.fetch = (url, options) => {
      expect(url.toString()).toBe("https://getkirby.com/test");
      expect(options.method).toBe("GET");
      expect(options.cache).toBe("no-store");
      expect(options.credentials).toBe("same-origin");
      expect(options.headers["X-Fiber"]).toBe(true);
      expect(options.headers["X-Fiber-Globals"]).toBe(false);
      expect(options.headers["X-Fiber-Only"]).toBe("");

      return {
        headers: new Headers({ "X-Fiber": true }),
        text: () =>
          JSON.stringify({
            $view: {
              test: "Test"
            }
          })
      };
    };

    const result = await fiber.request("test");
    expect(result).toEqual({
      $view: {
        test: "Test"
      }
    });
  });

  it("should request partial $view request", async () => {
    let fiber = new Fiber({
      base: "https://getkirby.com/"
    });

    fiber.state = {
      $view: {
        text: "Test text"
      }
    };

    // fetch mock
    fiber.fetch = () => {
      return {
        headers: new Headers({ "X-Fiber": true }),
        text: () =>
          JSON.stringify({
            $view: {
              title: "Test"
            }
          })
      };
    };

    const result = await fiber.request("test", {
      only: "$view.title"
    });

    expect(result).toEqual({
      $view: {
        title: "Test",
        text: "Test text"
      }
    });
  });

  it("should send a $dropdown request", async () => {
    let fiber = new Fiber({
      base: "https://getkirby.com/"
    });

    // fetch mock
    fiber.fetch = () => {
      return {
        headers: new Headers({ "X-Fiber": true }),
        text: () =>
          JSON.stringify({
            $dropdown: {
              options: []
            }
          })
      };
    };

    const result = await fiber.request("test", {
      type: "$dropdown"
    });

    expect(result).toEqual({
      options: []
    });
  });

  it("should send fatal event if json is not parseable", async () => {
    let fatal = null;
    let fiber = new Fiber({
      base: "https://getkirby.com/",
      onFatal: (args) => {
        fatal = args;
      }
    });

    const response = {
      headers: new Headers({ "X-Fiber": true }),
      text: async () => "No valid json response"
    };

    fiber.fetch = () => response;

    const result = await fiber.request("test");
    expect(result).toBe(false);

    expect(fatal.url.toString()).toBe("https://getkirby.com/test");
    expect(fatal.path).toBe("test");
    expect(fatal.text).toBe("No valid json response");
    expect(fatal.options.method).toBe("GET");
    expect(fatal.response).toEqual(response);
  });

  it("should fire request events", async () => {
    let started = false;
    let finished = false;

    let fiber = new Fiber({
      base: "https://getkirby.com/",
      onStart: () => {
        started = true;
      },
      onFinish: () => {
        finished = true;
      }
    });

    fiber.fetch = () => {
      return {
        headers: new Headers({ "X-Fiber": true }),
        text: () =>
          JSON.stringify({
            $view: {
              test: "Test"
            }
          })
      };
    };

    await fiber.request("test");

    expect(started).toBe(true);
    expect(finished).toBe(true);
  });

  it("should redirect to non-fiber resource", async () => {
    let fiber = new Fiber({
      base: "https://getkirby.com/"
    });

    let redirectTo = null;

    fiber.redirect = (url) => {
      redirectTo = url;
    };

    fiber.fetch = () => {
      return {
        headers: new Headers({}),
        url: "https://getkirby.com/redirected"
      };
    };

    const result = await fiber.request("test");
    expect(result).toBe(false);
    expect(redirectTo).toBe("https://getkirby.com/redirected");
  });

  it("should throw custom error", async () => {
    let fiber = new Fiber({
      base: "https://getkirby.com/"
    });

    fiber.fetch = () => {
      return {
        headers: new Headers({ "X-Fiber": true }),
        text: () =>
          JSON.stringify({
            $view: { error: "Custom error" }
          })
      };
    };

    try {
      await fiber.request("test");
    } catch (e) {
      expect(e.message).toBe("Custom error");
    }
  });

  it("should push the state", async () => {
    const state = { foo: "bar" };
    const options = { silent: true };

    let didSwap = false;
    let didPush = false;

    let fiber = new Fiber({
      base: "https://getkirby.com",
      onPushState: (newState, passedOptions) => {
        didPush = true;
        expect(newState).toEqual(state);
        expect(passedOptions).toEqual(options);
      },
      onSwap: (newState, passedOptions) => {
        didSwap = true;
        expect(newState).toEqual(state);
        expect(passedOptions).toEqual(options);
      }
    });

    const newState = await fiber.setState(state, options);

    expect(newState).toEqual(state);
    expect(didSwap).toBe(true);
    expect(didPush).toBe(true);
  });

  it("should replace the state", async () => {
    const state = { foo: "bar" };
    const options = { replace: true };

    let didSwap = false;
    let didReplace = false;

    let fiber = new Fiber({
      base: "https://getkirby.com",
      onReplaceState: (newState, passedOptions) => {
        didReplace = true;
        expect(newState).toEqual(state);
        expect(passedOptions).toEqual(options);
      },
      onSwap: (newState, passedOptions) => {
        didSwap = true;
        expect(newState).toEqual(state);
        expect(passedOptions).toEqual(options);
      }
    });

    const newState = await fiber.setState(state, options);

    expect(newState).toEqual(state);
    expect(didSwap).toBe(true);
    expect(didReplace).toBe(true);
  });

  it("should ignore the state if not an object", async () => {
    let fiber = new Fiber();
    expect(await fiber.setState("foo")).toBe(false);
  });

  it("should build the query", () => {
    let fiber = new Fiber();

    // invalid queries should be empty
    expect(fiber.query("foo").toString()).toBe("");

    // build valid queries
    expect(fiber.query({ a: "a", b: "b" }).toString()).toBe("a=a&b=b");

    // ignore null values
    expect(fiber.query({ a: "a", b: null }).toString()).toBe("a=a");

    // globals
    fiber.options.query = () => {
      return {
        a: "a"
      };
    };

    // include globals
    expect(fiber.query({ b: "b" }).toString()).toBe("b=b&a=a");

    // Keep search params from base object
    const baseObject = { a: "a" };
    expect(fiber.query({ b: "b" }, baseObject).toString()).toBe("a=a&b=b");

    // Keep search params from base string
    const baseString = "a=a";
    expect(fiber.query({ b: "b" }, baseString).toString()).toBe("a=a&b=b");

    // Keep search params from base params
    const baseParams = new URLSearchParams("a=a");
    expect(fiber.query({ b: "b" }, baseParams).toString()).toBe("a=a&b=b");
  });

  it("should build the URL", () => {
    const fiber = new Fiber({
      base: "https://getkirby.com/"
    });

    // base URL
    expect(fiber.url().toString()).toEqual("https://getkirby.com/");

    // added path
    expect(fiber.url("test").toString()).toEqual("https://getkirby.com/test");

    // added query
    expect(fiber.url("test", { a: "a" }).toString()).toEqual(
      "https://getkirby.com/test?a=a"
    );

    // external URLs
    expect(fiber.url("https://google.com").toString()).toEqual(
      "https://google.com/"
    );
  });
});
