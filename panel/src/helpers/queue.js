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
				next();
			} else if (active === 0 && index === tasks.length) {
				done(tasks);
			}
		};

		next();
	});
}
