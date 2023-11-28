/**
 * Simple timer implementation to
 * help with starting and stopping timers
 * @since 4.0.0
 *
 * @example
 * timer.start(100, () => {});
 * timer.stop();
 */
export default {
	interval: null,

	/**
	 * Starts the timer if a timeout is defined
	 *
	 * @param {Integer} timeout
	 * @param {Function} callback
	 */
	start(timeout, callback) {
		// stop any previous timers
		this.stop();

		// don't set a new one without a timeout
		if (!timeout) {
			return;
		}

		// set a new timer
		this.interval = setInterval(callback, timeout);
	},

	/**
	 * Stops the timer
	 */
	stop() {
		clearInterval(this.interval);
		this.interval = null;
	}
};
