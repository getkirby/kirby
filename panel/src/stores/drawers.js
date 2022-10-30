export default {
	/**
	 * State
	 */
	state: {
		open: []
	},

	/**
	 * Actions
	 */
	close(drawer) {
		if (drawer) {
			this.state.open = this.state.open.filter((item) => item.id !== drawer);
		} else {
			this.state.open = [];
		}
	},
	goto(drawer) {
		this.state.open = this.state.open.slice(
			0,
			this.state.open.findIndex((item) => item.id === drawer) + 1
		);
	},
	open(drawer) {
		this.state.open.push(drawer);
	}
};
