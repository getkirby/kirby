import Vue from "vue";

/**
 * Checks if the coponent is registered globally
 */
export default (name: string): boolean => {
	if (Vue.options.components[name] !== undefined) {
		return true;
	}

	return false;
};
