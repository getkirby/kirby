/**
 * Async function that runs a list of async functions
 * with a max number of concurrent tasks
 *
 * @param {Array} tasks list of async functions
 * @param {Number} concurrent max number of concurrent tasks
 * @returns {Promise<Array>}
 */
export default async function (tasks, concurrent = 20) {
	let active = 0;
	let index = 0;

	return new Promise((done) => {
		const resolve = (index) => (result) => {
			tasks[index] = result;
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
				done(tasks);
			}
		};

		next();
	});
}
