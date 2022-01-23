/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import dialog from "./dialog.js";

const Vue = () => {
  return {
    $fiber: {
      request() {
        return {
          component: "k-remove-dialog"
        };
      }
    },
    $helper: {
      isComponent() {
        return true;
      }
    },
    $store: {
      state: {},
      dispatch(state, value) {
        this.state[state] = value;
      }
    }
  };
};

describe.concurrent("$dialog()", () => {
  it("should create synchronous dialog", async () => {
    const result = await dialog.call(Vue(), {
      component: "k-remove-dialog",
      props: {
        text: "Test text"
      }
    });

    expect(result).toEqual({
      cancel: null,
      submit: null,
      component: "k-remove-dialog",
      props: {
        text: "Test text"
      }
    });
  });

  it("should always inject props", async () => {
    const result = await dialog.call(Vue(), {
      component: "k-remove-dialog"
    });

    expect(result).toEqual({
      cancel: null,
      submit: null,
      props: {},
      component: "k-remove-dialog"
    });
  });

  it("should update the store", async () => {
    const vue = Vue();

    await dialog.call(vue, {
      component: "k-remove-dialog"
    });

    expect(vue.$store.state.dialog).toEqual({
      cancel: null,
      submit: null,
      props: {},
      component: "k-remove-dialog"
    });
  });

  it("should fail on missing component", async () => {
    try {
      await dialog.call(Vue(), {});
    } catch (e) {
      expect(e.message).toEqual("The dialog component does not exist");
    }
  });

  it("should fail on invalid component", async () => {
    let vue = Vue();

    vue.$helper.isComponent = () => {
      return false;
    };

    try {
      await dialog.call(vue, {
        component: "k-custom-dialog"
      });
    } catch (e) {
      expect(e.message).toEqual("The dialog component does not exist");
    }
  });

  it("should create asynchronous dialog", async () => {
    const vue = Vue();

    const result = await dialog.call(vue, "test");

    expect(result).toEqual({
      component: "k-remove-dialog",
      props: {},
      submit: null,
      cancel: null
    });
  });

  it("should support custom handlers", async () => {
    const onSubmit = () => {};
    const onCancel = () => {};

    const result = await dialog.call(Vue(), "test", {
      submit: onSubmit,
      cancel: onCancel
    });

    expect(result.submit).toStrictEqual(onSubmit);
    expect(result.cancel).toStrictEqual(onCancel);
  });

  it("should support submit handler as second argument", async () => {
    const vue = Vue();

    vue.$fiber.request = async function () {
      return {
        component: "k-remove-dialog"
      };
    };

    const onSubmit = () => {
      return "submitted";
    };

    const result = await dialog.call(vue, "test", onSubmit);

    expect(result.submit).toStrictEqual(onSubmit);
  });

  it("should return false on invalid response", async () => {
    const vue = Vue();

    vue.$fiber.request = async function () {
      return false;
    };

    const result = await dialog.call(vue, "test");

    expect(result).toBe(false);
  });

  it("should prefix the request path", async () => {
    const vue = Vue();

    vue.$fiber.request = async function (path) {
      expect(path).toBe("dialogs/test");
    };

    await dialog.call(vue, "test");
  });

  it("should define the $dialog type", async () => {
    const vue = Vue();

    vue.$fiber.request = async function (path, options) {
      expect(options.type).toBe("$dialog");
    };

    await dialog.call(vue, "test");
  });
});
