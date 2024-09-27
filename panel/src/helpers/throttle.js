export default (func, delay, options = { leading: true, trailing: false }) => {
	let timer = null,
		lastRan = null,
		trailingArgs = null;

	return function (...args) {
		if (timer) {
			//called within cooldown period
			lastRan = this; //update context
			trailingArgs = args; //save for later
			return;
		}

		if (options.leading) {
			// if leading
			func.call(this, ...args); //call the 1st instance
		} else {
			// else it's trailing
			lastRan = this; //update context
			trailingArgs = args; //save for later
		}

		const coolDownPeriodComplete = () => {
			if (options.trailing && trailingArgs) {
				// if trailing and the trailing args exist
				func.call(lastRan, ...trailingArgs); //invoke the instance with stored context "lastRan"
				lastRan = null; //reset the status of lastRan
				trailingArgs = null; //reset trailing arguments
				timer = setTimeout(coolDownPeriodComplete, delay); //clear the timout
			} else {
				timer = null; // reset timer
			}
		};

		timer = setTimeout(coolDownPeriodComplete, delay);
	};
};
