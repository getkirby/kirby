/**
 * @since 4.0.0
 */
export default () => {
	return {
		close() {
			sessionStorage.setItem("kirby$activation$card", "true");
			this.isOpen = false;
		},

		isOpen: sessionStorage.getItem("kirby$activation$card") !== "true",

		open() {
			sessionStorage.removeItem("kirby$activation$card");
			this.isOpen = true;
		}
	};
};
