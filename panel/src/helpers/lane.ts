interface Caller {
	reject(reason: unknown): void;
	resolve(value: unknown): void;
}

interface Entry {
	callers: Caller[];
	key: string;
	task: () => Promise<unknown>;
}

/**
 * Creates a lane that runs async tasks one after another
 * in the order they were added. A task added under a key
 * that is still waiting replaces the waiting one and moves
 * to the end of the lane; whoever awaited the replaced task
 * receives the replacement's result.
 *
 * @example
 * const run = lane();
 * run("save", () => fetch(url));
 * run("save", () => fetch(url)); // replaces the waiting save
 * run.isBusy(); // true until both have finished
 */
export default function () {
	const waiting: Entry[] = [];
	let running = false;

	async function process() {
		running = true;

		for (let entry = waiting.shift(); entry; entry = waiting.shift()) {
			try {
				const result = await entry.task();
				entry.callers.forEach((caller) => caller.resolve(result));
			} catch (error) {
				entry.callers.forEach((caller) => caller.reject(error));
			}
		}

		running = false;
	}

	function run<T>(key: string, task: () => Promise<T>): Promise<T> {
		return new Promise<T>((resolve, reject) => {
			const index = waiting.findIndex((entry) => entry.key === key);
			const callers = index === -1 ? [] : waiting.splice(index, 1)[0].callers;

			waiting.push({ callers: [...callers, { reject, resolve }], key, task });

			if (running === false) {
				process();
			}
		});
	}

	// Add isBusy method to check for running or waiting tasks
	run.isBusy = (): boolean => running;

	return run;
}
