/**
 * @vitest-environment node
 */

import dialog from "./dialog.js";

const Vue = () => {
  return {
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
    const input = {
      component: "k-remove-dialog",
      props: {
        text: "Test text"
      }
    };

    const result = await dialog.call(Vue(), input);

    expect(result).toStrictEqual(input);
  });

  it("should always inject props", async () => {
    const result = await dialog.call(Vue(), {
      component: "k-remove-dialog"
    });

    expect(result).toEqual({
      component: "k-remove-dialog",
      props: {}
    });
  });

  it("should update the store", async () => {
    const vue = Vue();

    await dialog.call(vue, {
      component: "k-remove-dialog"
    });

    expect(vue.$store.state.dialog).toEqual({
      component: "k-remove-dialog",
      props: {}
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
});
