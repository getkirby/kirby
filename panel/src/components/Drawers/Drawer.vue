<template>
	<k-overlay
		ref="overlay"
		:autofocus="autofocus"
		:dimmed="!nested"
		:loading="loading"
		:visible="visible"
		class="k-drawer-overlay"
		@cancel="cancel"
		@ready="ready"
	>
		<k-drawer-box :id="id" :nested="nested">
			<k-drawer-form @submit="submit">
				<k-drawer-notification
					v-if="notification"
					v-bind="notification"
					@close="notification = null"
				/>
				<k-drawer-header
					:breadcrumb="breadcrumb"
					:icon="icon"
					:tab="tab"
					:tabs="tabs"
					:title="title"
					@openCrumb="openCrumb"
					@openTab="openTab"
				>
					<slot name="options" />
				</k-drawer-header>
				<k-drawer-body>
					<slot />
				</k-drawer-body>
			</k-drawer-form>
		</k-drawer-box>
	</k-overlay>
</template>

<script>
import { props as Overlay } from "@/components/Layout/Overlay.vue";

export const props = {
	mixins: [Overlay],
	props: {
		id: String,
		icon: String,
		tabs: {
			default: () => {},
			type: [Array, Object]
		},
		title: String
	}
};

export default {
	mixins: [props],
	data() {
		return {
			notification: null,
			tab: {}
		};
	},
	computed: {
		breadcrumb() {
			return this.$store.state.drawers.open;
		},
		index() {
			return this.breadcrumb.findIndex((item) => item.id === this._uid);
		},
		nested() {
			return this.index > 0;
		}
	},
	watch: {
		index() {
			if (this.index === -1) {
				this.close();
			}
		},
		tabs() {
			// open the first tab
			// when tabs change
			this.openTab();
		}
	},
	destroyed() {
		this.$store.dispatch("drawers/close", this._uid);
	},
	methods: {
		/**
		 * Triggers the `@cancel` event and closes the dialog.
		 * @public
		 */
		cancel() {
			/**
			 * This event is triggered whenever the cancel button or
			 * the backdrop is clicked.
			 * @event cancel
			 */
			this.$emit("cancel");
			this.close();
		},
		close() {
			this.notification = null;

			/**
			 * This event is triggered when the drawer is being closed.
			 * This happens independently from the cancel event.
			 * @event close
			 */
			this.$emit("close");
			this.$store.dispatch("drawers/close", this._uid);

			/**
			 * close the overlay if it is still there
			 * in fiber drawers the entire drawer component gets destroyed
			 * and this step is not necessary
			 */
			this.$refs.overlay?.close();
		},
		/**
		 * The overlay component has a built-in focus
		 * method that finds the best first element to
		 * focus on
		 */
		focus() {
			this.$refs.overlay.focus();
		},
		goTo(id) {
			if (id === this._uid) {
				return true;
			}

			this.notification = null;
			this.$store.dispatch("drawers/goto", id);
		},
		open() {
			// show the overlay
			this.$refs.overlay.open();

			/**
			 * This event is triggered as soon as the drawer is being opened.
			 * @event open
			 */
			this.$emit("open");
		},
		openCrumb(crumb) {
			this.goTo(crumb.id);
			this.$emit("openCrumb", crumb);
		},
		openTab(tab) {
			tab = tab || Object.values(this.tabs)[0] || {};

			if (!tab) {
				return false;
			}

			this.tab = tab;
			this.$emit("openTab", tab);
		},
		ready() {
			// when drawers are used in the old-fashioned way
			// by adding their component to a template and calling
			// open on the component manually, the drawer state
			// is set to a minimum. In comparison, this.$drawer fills
			// the drawer state after a successfull request and
			// the fiber drawer component is injected on store change
			// automatically.
			this.$store.dispatch("drawers/open", {
				id: this._uid,
				icon: this.icon,
				title: this.title
			});

			// close any notifications if there's still an open one
			this.notification = null;

			/**
			 * Mark the drawer as ready to be used
			 * @event ready
			 */
			this.$emit("ready");

			// open the first tab
			this.openTab();
		},
		/**
		 * This event is triggered when the submit button is clicked,
		 * or the form is submitted. It can also be called manually.
		 * @public
		 */
		submit() {
			/**
			 * @event submit
			 */
			this.$emit("submit");
		}
	}
};
</script>
