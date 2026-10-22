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
 * that is still waiting replaces the waiting one; whoever
 * awaited the replaced task receives the replacement's result.
 *
 * @example
 * const run = lane();
 * run("save", () => fetch(url));
 * run("save", () => fetch(url)); // replaces the waiting save
 */
export default function () {
	const waiting: Entry[] = [];
	let running = false;

	async function drain() {
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

	return function <T>(key: string, task: () => Promise<T>): Promise<T> {
		return new Promise<T>((resolve, reject) => {
			const caller = { reject, resolve };
			const entry = waiting.find((entry) => entry.key === key);

			if (entry) {
				entry.task = task;
				entry.callers.push(caller);
			} else {
				waiting.push({ callers: [caller], key, task });
			}

			if (running === false) {
				drain();
			}
		});
	};
}
