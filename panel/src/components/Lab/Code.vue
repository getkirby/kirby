<template>
	<k-code :language="language"><slot /></k-code>
</template>

<script>
window.Prism = window.Prism || {};
window.Prism.manual = true;

import "./prism/prism.js";
import "./prism/prism.css";

export default {
	props: {
		language: String
	},
	mounted() {
		Prism.highlightAll(this.$el);
	}
};
</script>
