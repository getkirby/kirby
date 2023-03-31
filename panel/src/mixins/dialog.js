import { props as Dialog } from "@/components/Dialogs/Dialog.vue";

/**
 * The Dialog mixin is intended for all components
 * that extend <k-dialog> It forwards the methods to
 * the <k-dialog> ref. Extending <k-dialog> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	mixins: [Dialog],
	methods: {
		cancel() {
			this.$refs.dialog.cancel();
		},
		close() {
			this.$refs.dialog.close();
		},
		error(error) {
			this.$refs.dialog.error(error);
		},
		focus() {
			this.$refs.dialog.focus();
		},
		open() {
			this.$refs.dialog.open();
		},
		submit() {
			this.$refs.dialog.submit();
		},
		success(success) {
			this.$refs.dialog.success(success);
		}
	}
};
