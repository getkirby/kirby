import store from "@/store/store.js";

describe("Content Store", () => {

  it("default state", () => {

    expect(store.state.content.current).toEqual(null);
    expect(store.state.content.models).toEqual({});
    expect(store.state.content.status.enabled).toEqual(true);
    expect(store.state.content.status.lock).toEqual(null);
    expect(store.state.content.status.unlock).toEqual(null);

  });

  it("default getters", () => {

    expect(store.getters["content/exists"]("pages/test")).toEqual(false);
    expect(store.getters["content/hasChanges"]("pages/test")).toEqual(false);
    expect(store.getters["content/isCurrent"]("pages/test")).toEqual(false);
    expect(store.getters["content/id"]("pages/test")).toEqual("pages/test");
    expect(store.getters["content/model"]("pages/test")).toEqual({
      api: null,
      originals: {},
      values: {},
      changes: {},
    });

    expect(store.getters["content/originals"]("pages/test")).toEqual({});
    expect(store.getters["content/values"]("pages/test")).toEqual({});
    expect(store.getters["content/changes"]("pages/test")).toEqual({});

  });

  it("set current", () => {
    expect(store.state.content.current).toEqual(null);
    store.dispatch("content/current", "pages/test");
    expect(store.getters["content/isCurrent"]("pages/test")).toEqual(true);
  });

  it("enable/disable content editing", () => {
    expect(store.state.content.status.enabled).toEqual(true);
    store.dispatch("content/disable");
    expect(store.state.content.status.enabled).toEqual(false);
    store.dispatch("content/enable");
    expect(store.state.content.status.enabled).toEqual(true);
  });

  it("create and remove", () => {

    expect(store.getters["content/exists"]("pages/test")).toEqual(false);

    // use commit, because dispatch makes an API request
    store.commit("content/CREATE", ["pages/test", {
      api: "pages/test",
      originals: {
        title: "Test"
      }
    }]);

    expect(store.getters["content/exists"]("pages/test")).toEqual(true);
    expect(store.getters["content/model"]("pages/test")).toEqual({
      api: "pages/test",
      changes: {},
      originals: {
        title: "Test"
      }
    });

    // remove the model from the store again
    store.dispatch("content/remove", "pages/test");

    expect(store.getters["content/exists"]("pages/test")).toEqual(false);

  });

  it("create, set current and remove", () => {

    expect(store.getters["content/exists"]("pages/test")).toEqual(false);

    // use commit, because dispatch makes an API request
    store.commit("content/CREATE", ["pages/test", {
      api: "pages/test",
      originals: {
        title: "Test"
      }
    }]);

    store.dispatch("content/current", "pages/test");

    expect(store.getters["content/isCurrent"]("pages/test")).toEqual(true);
    expect(store.getters["content/exists"]("pages/test")).toEqual(true);

    // remove the model from the store again
    store.dispatch("content/remove", "pages/test");

    expect(store.getters["content/isCurrent"]("pages/test")).toEqual(false);
    expect(store.getters["content/exists"]("pages/test")).toEqual(false);
  });

  it("create and move", () => {

    // use commit, because dispatch makes an API request
    store.commit("content/CREATE", ["pages/a", {
      api: "pages/test",
      originals: {
        title: "Test"
      }
    }]);

    expect(store.getters["content/exists"]("pages/a")).toEqual(true);
    expect(store.getters["content/exists"]("pages/b")).toEqual(false);

    // move the model to a new key
    store.dispatch("content/move", ["pages/a", "pages/b"]);

    expect(store.getters["content/exists"]("pages/a")).toEqual(false);
    expect(store.getters["content/exists"]("pages/b")).toEqual(true);

    // clean up
    store.dispatch("content/remove", "pages/b");

  });

  it("create, update and revert", () => {

    // use commit, because dispatch makes an API request
    store.commit("content/CREATE", ["pages/test", {
      api: "pages/test",
      originals: {
        title: "Test"
      }
    }]);

    expect(store.getters["content/originals"]("pages/test")).toEqual({
      title: "Test"
    });

    expect(store.getters["content/values"]("pages/test")).toEqual({
      title: "Test"
    });

    expect(store.getters["content/changes"]("pages/test")).toEqual({});
    expect(store.getters["content/hasChanges"]("pages/test")).toEqual(false);

    // make some changes
    store.dispatch("content/update", ["title", "Updated", "pages/test"]);

    expect(store.getters["content/originals"]("pages/test")).toEqual({
      title: "Test"
    });

    expect(store.getters["content/values"]("pages/test")).toEqual({
      title: "Updated"
    });

    expect(store.getters["content/changes"]("pages/test")).toEqual({
      title: "Updated"
    });

    expect(store.getters["content/hasChanges"]("pages/test")).toEqual(true);

    // revert changes
    store.dispatch("content/revert", "pages/test");

    expect(store.getters["content/originals"]("pages/test")).toEqual({
      title: "Test"
    });

    expect(store.getters["content/values"]("pages/test")).toEqual({
      title: "Test"
    });

    expect(store.getters["content/changes"]("pages/test")).toEqual({});
    expect(store.getters["content/hasChanges"]("pages/test")).toEqual(false);

    // clean up
    store.dispatch("content/remove", "pages/test");

  });

});
