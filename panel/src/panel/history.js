import { reactive } from "vue";

/**
 * @since 4.0.0
 */
export default () => {
	return reactive({
		add(state, replace = false) {
			if (!state.id) {
				throw new Error("The state needs an ID");
			}

			if (replace === true) {
				return this.replace(-1, state);
			}

			// the state is already in the history
			if (this.has(state.id) === true) {
				return;
			}

			this.milestones.push(state);
		},
		at(index) {
			return this.milestones.at(index);
		},
		clear() {
			this.milestones = [];
		},
		get(id = null) {
			if (id === null) {
				return this.milestones;
			}

			return this.milestones.find((milestone) => milestone.id === id);
		},
		goto(id) {
			const index = this.index(id);

			if (index === -1) {
				return undefined;
			}

			// remove all items after this
			this.milestones = this.milestones.slice(0, index + 1);

			return this.milestones[index];
		},
		has(id) {
			return this.get(id) !== undefined;
		},
		index(id) {
			return this.milestones.findIndex((milestone) => milestone.id === id);
		},
		isEmpty() {
			return this.milestones.length === 0;
		},
		last() {
			return this.milestones.at(-1);
		},
		milestones: [],
		remove(id = null) {
			if (id === null) {
				return this.removeLast();
			}

			return (this.milestones = this.milestones.filter(
				(milestone) => milestone.id !== id
			));
		},
		removeLast() {
			return (this.milestones = this.milestones.slice(0, -1));
		},
		replace(index, state) {
			if (index === -1) {
				index = this.milestones.length - 1;
			}

			this.milestones[index] = state;
		}
	});
};
