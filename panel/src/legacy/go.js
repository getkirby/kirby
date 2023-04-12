/**
 * Opens a view at the given URL
 *
 * @param  {String} url
 * @param  {Object} options
 * @returns {Object} Returns the new view state
 */
export default async (url, options = {}) => {
	return window.panel.view.open(url, options);
};
