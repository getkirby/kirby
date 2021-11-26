export default class Emitter {
  emit(event, ...args) {
    this._callbacks = this._callbacks || {};
    const callbacks = this._callbacks[event];

    if (callbacks) {
      callbacks.forEach((callback) => callback.apply(this, args));
    }

    return this;
  }

  /**
   * Remove event listener for given event.
   * If fn is not provided, all event listeners for that event will be removed.
   * If neither is provided, all event listeners will be removed.
   */
  off(event, fn) {
    if (!arguments.length) {
      this._callbacks = {};
    } else {
      // event listeners for the given event
      const callbacks = this._callbacks ? this._callbacks[event] : null;
      if (callbacks) {
        if (fn) {
          // remove specific handler
          this._callbacks[event] = callbacks.filter((cb) => cb !== fn);
        } else {
          // remove all handlers
          delete this._callbacks[event];
        }
      }
    }

    return this;
  }

  /**
   * Add an event listener for given event
   */
  on(event, fn) {
    this._callbacks = this._callbacks || {};
    this._callbacks[event] = this._callbacks[event] || [];
    this._callbacks[event].push(fn);
    return this;
  }
}
