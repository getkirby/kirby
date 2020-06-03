

export default store => {

  let supported = true;
  let heartbeat = null;

  const repeat = (callback, seconds) => {
    clearInterval(heartbeat);
    heartbeat = setInterval(callback, seconds * 1000);
    callback();
  }

  const getLock = async () => {
    try {
      // TODO: API call
      const response = await this.$api.get();

      // if content locking is not supported by model,
      // set flag and stop listening
      if (response.supported === false) {
        supported = false;
        return clearInterval(heartbeat);
      }

      // if content is locked, dispatch info to store
      if (response.locked !== false) {
        return store.dispatch("content/lock", response.locked);
      }

      store.dispatch("content/lock", null);

    } catch (error) {
      // fail silently
    }
  };

  const setLock = async () => {
    if (supported === true) {
      try {
        // TODO: API call
        await Vue.$api.patch();

      } catch (error) {
        // turns out: locking is not supported
        if (error.key === "error.lock.notImplemented") {
          supported = false;
          return clearInterval(heartbeat);
        }

        // If setting lock failed, a competing lock has been set between
        // API calls. In that case, discard changes.
        store.dispatch("content/revert");
      }
    }
  };

  const removeLock = async () => {
    if (supported === true) {
      clearInterval(heartbeat);

      try {
        // TODO: API Call
        await this.$api.delete();
        store.dispatch("content/lock", null);
        repeat(getLock, 10);

      } catch (error) {
        // fail silently
      }
    }
  }

  const onRouting = () => {
    repeat(getLock, 10);
  }

  const onChanges = hasChanges => {
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
    if (action.type === "content/revert") {
      return removeLock();
    }
    if (action.type === "content/update") {
      return removeLock();
    }
  })
}
