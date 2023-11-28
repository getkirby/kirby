export default {
	props: {
		/**
		 * A unique ID. The component `_uid` will be used as default.
		 */
		id: {
			type: [Number, String],
			default() {
				return this._uid;
			}
		}
	}
};
