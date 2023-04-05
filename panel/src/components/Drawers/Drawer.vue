<template>
	<k-overlay ref="overlay" type="drawer" @cancel="cancel" @ready="ready">
		<form class="k-drawer" method="dialog" @submit.prevent="submit">
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
		</form>
	</k-overlay>
</template>

<script>
export const props = {
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
			tab: null
		};
	},
	computed: {
		breadcrumb() {
			return this.$store.state.drawers.open;
		},
		index() {
			return this.$store.state.drawers.open.findIndex(
				(item) => item.id === this._uid
			);
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
			tab = tab || Object.keys(this.tabs)[0];

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

<style>
:root {
	--drawer-color-back: var(--color-light);
	--drawer-header-height: 2.5rem;
	--drawer-header-padding: 1.5rem;
	--drawer-shadow: var(--shadow-xl);
	--drawer-width: 50rem;
}

.k-drawer-overlay {
	--overlay-color-back: rgba(0, 0, 0, 0.2);
	display: flex;
	align-items: stretch;
	justify-content: flex-end;
}

/**
 * Don't apply the dark background twice
 * for nested drawers.
 */
.k-drawer-overlay + .k-drawer-overlay {
	--overlay-color-back: none;
}

.k-drawer {
	z-index: var(--z-toolbar);
	display: flex;
	flex-basis: var(--drawer-width);
	position: relative;
	display: flex;
	flex-direction: column;
	background: var(--drawer-color-back);
	box-shadow: var(--drawer-shadow);
}

/* Scroll lock */
:where(body):has(.k-drawer) {
	overflow: hidden;
}
</style>
