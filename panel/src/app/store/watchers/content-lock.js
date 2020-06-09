

export default (Vue, store) => {

  /**
   * internal states
   */

  let heartbeat = null;

  /**
   * Helpers
   */

  const repeat = (callback, seconds, immediate = false) => {
    clearInterval(heartbeat);
    heartbeat = setInterval(callback, seconds * 1000);
    if (immediate) {
      callback();
    }
  }

  const toEndpoint = (path) => {
    let endpoint = store.getters["content/api"](store.state.content.current);

    if (path) {
      endpoint += "/" + path;
    }

    return endpoint;
  }

  /**
   * Actions
   */

  const getLock = async () => {
    const response = await Vue.$api.get(
      toEndpoint("lock"),
      null,
      null,
      true
    );
    // console.log("+++ GET /lock: " + JSON.stringify(response));
    store.dispatch("content/lock", response.lock);
  };

  const setLock = async () => {
    try {
      // console.log("+++ PATCH /lock");
      await Vue.$api.patch(
        toEndpoint("lock"),
        null,
        null,
        true
      );

    } catch (error) {
      // If setting lock failed, a competing lock has been set between
      // API calls. In that case, discard unsaved changes.
      // console.log("-> setting log failed (e.g. competing lock), discard unsaved changes");
      store.dispatch("content/revert");
    }
  };

  const unsetLock = async () => {
    // console.log("+++ DELETE /lock");
    await Vue.$api.delete(
      toEndpoint("lock"),
      null,
      null,
      true
    );
    store.dispatch("content/lock", false);
  }

  const getUnlock = async () => {
    const response = await Vue.$api.get(
      toEndpoint("unlock"),
      null,
      null,
      true
    );
    // console.log("+++ GET /unlock: " + JSON.stringify(response));
    const isUnlocked = response.unlocked === true;
    store.dispatch("content/unlocked", isUnlocked)
    return isUnlocked;
  };

  const unsetUnlock = async() => {
    // console.log("+++ DELETE /unlock");
    await Vue.$api.delete(
      toEndpoint("unlock"),
      null,
      null,
      true
    );
  }

  /**
   * Triggered events
   */

  const onChangeModel = async (current) => {
    // console.log("+++++++++++++++++");
    // console.log("@onChangeModel: " + current);
    // console.log("-> clear heartbeat");
    clearInterval(heartbeat);

    const isSupported = store.state.content.locking.supported;

    // console.log("-> is locking supported? " + isSupported);
    if (isSupported === false) {
      // console.log("###")
      return;
    }

    // if model has unsaved changes, check for unlock
    const hasChanges = store.getters['content/hasChanges'](current);
    // console.log("-> already has unsaved changes? " + hasChanges);
    if (hasChanges === true) {
      // console.log("-> check if those were unlocked");
      const hasUnlock = await getUnlock();

      // has been unlocked
      if (hasUnlock) {
        // console.log("-> has been unlocked");
        // console.log("###");
        return;
      }
      // console.log("-> has not been unlocked, proceed")
    }

    // watch for unsaved changes
    // console.log("-> start watching for changes...")
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
      repeat(setLock, 30, true);

    // if there are no unsaved changes,
    // listen to lock from other users
    } else {
      // console.log("-> has no changes, getLock every 10/30 seconds")
      const hasLock = store.state.content.locking.lock !== false;
      repeat(getLock, hasLock ? 30 : 10);
    }
  };


  /**
   *  Watchers
   */

  store.watch(() => store.state.content.current, onChangeModel);

  store.subscribeAction((action, state) => {
    if (action.type === "content/revert") {
      // console.log("@onAction: " + action.type);
      return unsetLock();
    }

    if (action.type === "content/unlocked") {
      if (state.content.locking.unlocked !== false && action.payload === false) {
        // console.log("@onAction: " + action.type);
        // console.log("-> removing unlock");
        return unsetUnlock();
      }

    }
  });
}
