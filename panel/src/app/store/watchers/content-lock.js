

export default (Vue, store) => {

  /**
   * internal states
   */

  let model     = null;
  let supported = true;
  let heartbeat = null;

  /**
   * Helpers
   */

  const repeat = (callback, seconds) => {
    clearInterval(heartbeat);
    heartbeat = setInterval(callback, seconds * 1000);
    callback();
  }

  const toEndpoint = (storeId, path) => {
    storeId = store.getters["content/api"](storeId);

    if (path) {
      storeId += "/" + path;
    }

    return storeId;
  }

  /**
   * Actions
   */

  const getLock = async () => {
    const response = await Vue.$api.get(
      toEndpoint(store.state.content.current.id, "lock"),
      null,
      null,
      true
    );
    // console.log("+++ GET /lock: " + JSON.stringify(response));


    // if content locking is not supported by model,
    // set flag and stop listening
    if (response.supported === false) {
      // console.log("-> content locking not supported, set flag");
      // console.log("###");
      supported = false;
      return clearInterval(heartbeat);
    }

    store.dispatch("content/lock", response.locked);
  };

  const setLock = async () => {
    if (supported === true) {
      try {
        // console.log("+++ PATCH /lock");
        await Vue.$api.patch(
          toEndpoint(store.state.content.current.id, "lock"),
          null,
          null,
          true
        );

      } catch (error) {
        // turns out: locking is not supported
        if (error.key === "error.lock.notImplemented") {
          // console.log("-> content locking not supported, set flag");
          // console.log("###");
          supported = false;

          return clearInterval(heartbeat);
        }

        // If setting lock failed, a competing lock has been set between
        // API calls. In that case, discard unsaved changes.
        // console.log("-> setting log failed (e.g. competing lock), discard unsaved changes");
        store.dispatch("content/revert");
      }
    }
  };

  const unsetLock = async () => {
    if (supported === true) {
      // console.log("+++ DELETE /lock");
      await Vue.$api.delete(
        toEndpoint(store.state.content.current.id, "lock"),
        null,
        null,
        true
      );
      store.dispatch("content/lock", false);
    }
  }

  const getUnlock = async () => {
    const response = await Vue.$api.get(
      toEndpoint(store.state.content.current.id, "unlock"),
      null,
      null,
      true
    );
    // console.log("+++ GET /unlock: " + JSON.stringify(response));
    return response.supported === true && response.unlocked === true;
  };

  const unsetUnlock = async() => {
    // console.log("+++ DELETE /unlock");
    await Vue.$api.delete(
      toEndpoint(store.state.content.current.id, "unlock"),
      null,
      null,
      true
    );
  }

  /**
   * Triggered events
   */

  const onChangeModel = async (current) => {
    // console.log("@onLoadModel: " + current || "–");

    // stop if no current model is set (e.g. Settings view)
    if (current === null) {
      // console.log("-> no model set");
      // console.log("###");
      return clearInterval(heartbeat);
    }

    // console.log("-> model set");

    // if model has unsaved changes, check for unlock
    if (store.getters['content/hasChanges'](current)) {
      // console.log("-> has unsaved changes, check if unlocked");
      const hasUnlock = await getUnlock();

      // has been unlocked
      if (hasUnlock) {
        // console.log("-> has been unlocked");
        // console.log("###");
        return store.dispatch("content/unlocked", true);
      }
    }

    // watch for unsaved changes
    // console.log("-> start watching changes")
    store.watch(
      () => store.getters['content/hasChanges'](current),
      onChanges,
      { immediate: true }
    );
  };

  const onChanges = (hasChanges) => {
    // console.log("@onChanges: " + hasChanges);

    // if user started to make changes,
    // start setting lock every 30 seconds
    if (hasChanges) {
      // console.log("-> changes: " + JSON.stringify(store.getters["content/changes"]()));
      // console.log("-> has changes, setLock every 30 seconds")
      repeat(setLock, 30);

    // if there are no unsaved changes,
    // listen to lock from other users
    } else {
      // console.log("-> has no changes, getLock every 10 seconds")
      repeat(getLock, 10);
    }
  };


  /**
   *  Watchers
   */

  store.watch(() => store.state.content.current.id, onChangeModel);

  store.subscribeAction((action, state) => {
    if (action.type === "content/revert") {
      // console.log("@onAction: " + action.type);
      return unsetLock();
    }

    if (action.type === "content/unlocked") {
      if (state.content.current.unlocked !== false && action.payload === false) {
        // console.log("@onAction: " + action.type);
        // console.log("-> removing unlock");
        return unsetUnlock();
      }

    }
  });
}
