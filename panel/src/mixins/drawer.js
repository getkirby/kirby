import { props as Drawer } from "@/components/Drawers/Drawer.vue";

/**
 * The Drawer mixin is intended for all components
 * that extend <k-drawer> It forwards the methods to
 * the <k-drawer> ref. Extending <k-drawer> directly
 * can lead to breaking methods when the methods are not
 * wired correctly to the right elements and refs.
 */
export default {
	mixins: [Drawer],
	data() {
		return {
			currentFields: null,
			currentTab: null
		};
	},
	methods: {
		cancel() {
			this.$refs.drawer.cancel();
		},
		close() {
			this.$refs.drawer.close();
		},
		error(error) {
			this.$refs.drawer.error(error);
		},
		focus() {
			this.$refs.drawer.focus();
		},
		open() {
			this.$refs.drawer.open();
		},
		openCrumb(crumb) {
			this.$refs.drawer.openCrumb(crumb);
		},
		openTab(tab) {
			this.$refs.drawer.openCrumb(tab);
		},
		submit() {
			this.$refs.drawer.submit();
		},
		success(success) {
			this.$refs.drawer.success(success);
		}
	}
};
