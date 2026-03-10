/**
 * Async function that runs a list of async functions
 * with a max number of concurrent tasks
 *
 * @example
 * const tasks = urls.map((url) => () => fetch(url))
 * await queue(tasks, 5) // runs at most 5 fetches concurrently
 *
 * @param tasks - list of async functions
 * @param concurrent - max number of concurrent tasks
 */
export default async function <T>(
	tasks: (() => Promise<T>)[],
	concurrent: number = 20
): Promise<T[]> {
	const results: T[] = [];
	let active = 0;
	let index = 0;

	return new Promise((done) => {
		const resolve = (index: number) => (result: T) => {
			results[index] = result;
			active--;
			next();
		};

		const next = () => {
			if (active < concurrent && index < tasks.length) {
				tasks[index]().then(resolve(index)).catch(resolve(index));
				index++;
				active++;
				return next();
			}

			if (active === 0 && index === tasks.length) {
				done(results);
			}
		};

		next();
	});
}
