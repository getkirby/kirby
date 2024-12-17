/**
 * @since 5.0.0
 * @deprecated 5.0.0 Remove in 6.0.0
 */
export default (panel) => {
	return {
		/**
		 * Returns legacy content changes, stored in
		 * localStorage if they exist.
		 */
		changes(api) {
			const data = window.localStorage.getItem(this.id(api));

			if (!data) {
				return null;
			}

			return JSON.parse(data).changes;
		},
		cleanup(api) {
			const id = this.id(api);

			if (window.localStorage.getItem(id)) {
				this.log("Cleaning up legacy changes for", api);
				window.localStorage.removeItem(id);
			}
		},
		/**
		 * Returns the localstorage id for legacy changes
		 */
		id(api) {
			return `kirby$content$${api}?language=${panel.language.code}`;
		},
		async import(api) {
			const lock = panel.content.lock(api);

			if (lock.isLocked === true) {
				this.log("The content is locked by someone else", api);
				this.cleanup(api);
				return;
			}

			if (lock.isLegacy === false) {
				this.log("No legacy lock detected", api);
				this.cleanup(api);
				return;
			}

			if (lock.isActive === false) {
				this.log("The lock is no longer active", api);
				this.cleanup(api);
				return;
			}

			const changes = this.changes(api);

			if (!changes) {
				this.log("No valid legacy changes found", api);
				this.cleanup(api);
				return;
			}

			this.log("Importing legacy changes", api);
			await panel.content.update(changes, api);
		},
		log(message, api) {
			if (panel.debug) {
				console.info(`[Legacy changes] ${message}:`, api);
			}
		},
		simulate(values, api) {
			window.localStorage.setItem(
				this.id(api),
				JSON.stringify({
					changes: values
				})
			);
		}
	};
};
