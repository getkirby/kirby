

export default (Vue, store) => {

  let supported = true;
  let heartbeat = null;

  const repeat = (callback, seconds) => {
    clearInterval(heartbeat);
    heartbeat = setInterval(callback, seconds * 1000);
    callback();
  }

  const getLock = async () => {
    const response = await Vue.$api.get(
      store.state.content.current.id + "/lock",
      null,
      null,
      true
    );
    // console.log("-> getLock:");
    // console.log(response);

    // if content locking is not supported by model,
    // set flag and stop listening
    if (response.supported === false) {
      supported = false;
      return clearInterval(heartbeat);
    }

    store.dispatch("content/lock", response.locked);
  };

  const setLock = async () => {
    if (supported === true) {
      try {
        // console.log("-> setLock");
        await Vue.$api.patch(
          store.state.content.current.id + "/lock",
          null,
          null,
          true
        );

      } catch (error) {
        // turns out: locking is not supported
        if (error.key === "error.lock.notImplemented") {
          supported = false;
          return clearInterval(heartbeat);
        }

        // If setting lock failed, a competing lock has been set between
        // API calls. In that case, discard unsaved changes.
        store.dispatch("content/revert");
      }
    }
  };

  const removeLock = async () => {
    if (supported === true) {
      clearInterval(heartbeat);
      await Vue.$api.delete(
        store.state.content.current.id + "/lock",
        null,
        null,
        true
      );
      store.dispatch("content/lock", false);
    }
  }

  const onRouting = () => {
    if (store.state.content.current.id) {
      // console.log("@onRouting");
      repeat(getLock, 10);
    }
  }

  const onChanges = hasChanges => {
    // console.log("@onChanges: " + hasChanges);
    // console.log(store.getters["content/changes"]());

    // if user started to make changes,
    // start setting lock every 30 seconds
    if (hasChanges) {
      repeat(setLock, 30);

    // if there are no unsaved changes,
    // listen to lock from other users
    } else {
      repeat(getLock, 10);
    }
  }

  // watch when current model changes
  store.watch(() => store.state.content.current.id, onRouting);

  // watch for unsaved changes
  store.watch(() => store.getters['content/hasChanges'](), onChanges);

  // subcribe to actions that should alter content locking state
  store.subscribeAction((action, state) => {
    if (
      action.type === "content/revert" ||
      action.type === "content/update"
      ) {
      // console.log("@onAction: " + action.type);
      return removeLock();
    }
  })
}
