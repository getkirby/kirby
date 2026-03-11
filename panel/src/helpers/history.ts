/**
 * Tracks a list of state milestones and allows
 * retrieving previous milestones, adding new ones, etc.
 *
 * @since 5.4.0
 *
 * @example
 * const history = new History();
 * history.add({ id: "a", url: "/a" });
 * history.add({ id: "b", url: "/b" });
 * history.last;        // { id: "b", url: "/b" }
 * history.goto("a");   // { id: "a", url: "/a" }
 */
export default class History<T extends { id: string }> {
	milestones: T[] = [];

	add(state: T, replace?: boolean): void {
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
	}

	at(index: number): T | undefined {
		return this.milestones.at(index);
	}

	clear(): void {
		this.milestones = [];
	}

	get(id?: string): T[] | T | undefined {
		if (!id) {
			return this.milestones;
		}

		return this.milestones.find((milestone) => milestone.id === id);
	}

	goto(id: string): T | undefined {
		const index = this.index(id);

		if (index === -1) {
			return undefined;
		}

		// remove all items after this
		this.milestones = this.milestones.slice(0, index + 1);

		return this.milestones[index];
	}

	has(id: string): boolean {
		return this.get(id) !== undefined;
	}

	hasPrevious(): boolean {
		return this.milestones.length > 1;
	}

	index(id: string): number {
		return this.milestones.findIndex((milestone) => milestone.id === id);
	}

	isEmpty(): boolean {
		return this.milestones.length === 0;
	}

	last(): T | undefined {
		return this.milestones.at(-1);
	}

	remove(id?: string): T[] {
		if (!id) {
			return this.removeLast();
		}

		return (this.milestones = this.milestones.filter(
			(milestone) => milestone.id !== id
		));
	}

	removeLast(): T[] {
		return (this.milestones = this.milestones.slice(0, -1));
	}

	replace(index: number, state: T): void {
		if (index === -1) {
			index = this.milestones.length - 1;
		}

		this.milestones[index] = state;
	}
}
