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
			this.$panel.dialog.cancel();
		},
		close() {
			this.$panel.dialog.close();
		},
		error(error) {
			this.$panel.dialog.error(error);
		},
		focus() {
			this.$refs.dialog.focus();
		},
		input(value) {
			this.$panel.dialog.input(value);
		},
		open() {
			this.$panel.dialog.open(this);
		},
		ready() {
			this.$panel.dialog.emit("ready");
		},
		submit() {
			this.$panel.dialog.submit(this.panel.$value);
		},
		success(success) {
			this.$panel.dialog.success(success);
		}
	}
};
