<script>
/**
 * @deprecated 5.0.0 Use `k-writer-input` instead.
 */
export default {
	extends: "k-writer-input",
	mounted() {
		window.panel.deprecated(
			"`k-writer` will be removed in a future version. Use `k-writer-input` instead."
		);
	}
};
</script>
