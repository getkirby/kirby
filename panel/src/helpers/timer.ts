/**
 * Wraps setInterval to manage the start/stop lifecycle
 * of a repeating timer
 *
 * @example
 * const timer = new Timer();
 * timer.start(100, () => {});
 * timer.stop();
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     4.0.0
 */
export default class Timer {
	private interval: ReturnType<typeof setInterval> | undefined;

	get isRunning(): boolean {
		return this.interval !== undefined;
	}

	/**
	 * Starts the timer. Replaces any currently running timer.
	 */
	start(timeout: number, callback: () => void): void {
		// stop any previous timers
		this.stop();

		// don't set a new one without a timeout
		if (timeout <= 0) {
			return;
		}

		// set a new timer
		this.interval = setInterval(callback, timeout);
	}

	/**
	 * Stops the timer. Safe to call when not running.
	 */
	stop(): void {
		clearInterval(this.interval);
		this.interval = undefined;
	}
}
