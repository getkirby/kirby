<template>
	<k-overlay
		ref="overlay"
		:autofocus="autofocus"
		:dimmed="dimmed"
		:loading="loading"
		:visible="visible"
		class="k-drawer-overlay"
		@cancel="cancel"
		@ready="ready"
	>
		<div :data-id="id" :data-nested="nested" class="k-drawer">
			<k-drawer-notification
				v-if="notification"
				v-bind="notification"
				@close="notification = null"
			/>
			<header class="k-drawer-header">
				<h2 v-if="breadcrumb.length === 1" class="k-drawer-title">
					<k-icon :type="icon" /> {{ title }}
				</h2>
				<ul v-else class="k-drawer-breadcrumb">
					<li v-for="crumb in breadcrumb" :key="crumb.id">
						<k-button
							:icon="crumb.icon"
							:text="crumb.title"
							@click="goTo(crumb.id)"
						/>
					</li>
				</ul>
				<k-drawer-tabs :tab="tab" :tabs="tabs" @tab="$emit('tab', $event)" />
				<nav class="k-drawer-options">
					<slot name="options" />
					<k-button class="k-drawer-option" icon="check" @click="close" />
				</nav>
			</header>
			<k-drawer-body>
				<slot />
			</k-drawer-body>
		</div>
	</k-overlay>
</template>

<script>
import { props as Overlay } from "@/components/Layout/Overlay.vue";
import { props as Tabs } from "./Elements/Tabs.vue";

export const props = {
	mixins: [Overlay, Tabs],
	props: {
		id: String,
		icon: String,
		title: String
	}
};

export default {
	mixins: [props],
	data() {
		return {
			click: false,
			notification: null
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

.k-drawer-header {
	flex-shrink: 0;
	height: var(--drawer-header-height);
	padding-inline-start: var(--drawer-header-padding);
	display: flex;
	align-items: center;
	line-height: 1;
	justify-content: space-between;
	background: var(--color-white);
	font-size: var(--text-sm);
}
.k-drawer-title {
	padding: 0 0.75rem;
}
.k-drawer-title,
.k-drawer-breadcrumb {
	display: flex;
	flex-grow: 1;
	align-items: center;
	min-width: 0;
	margin-inline-start: -0.75rem;
	font-size: var(--text-sm);
	font-weight: var(--font-normal);
}
.k-drawer-breadcrumb li:not(:last-child) .k-button::after {
	position: absolute;
	inset-inline-end: -0.75rem;
	width: 1.5rem;
	display: inline-flex;
	justify-content: center;
	align-items: center;
	content: "›";
	color: var(--color-gray-500);
	height: var(--drawer-header-height);
}
.k-drawer-title .k-icon,
.k-drawer-breadcrumb .k-icon {
	width: 1rem;
	color: var(--color-gray-500);
	margin-inline-end: 0.5rem;
}
.k-drawer-breadcrumb .k-button {
	display: inline-flex;
	align-items: center;
	height: var(--drawer-header-height);
	padding-inline: 0.75rem;
}
.k-drawer-breadcrumb .k-button-text {
	opacity: 1;
}
.k-drawer-breadcrumb .k-button .k-button-icon ~ .k-button-text {
	padding-inline-start: 0;
}

.k-drawer-options {
	padding-inline-end: 0.75rem;
}
.k-drawer-option.k-button {
	width: var(--drawer-header-height);
	height: var(--drawer-header-height);
	color: var(--color-gray-500);
	line-height: 1;
}
.k-drawer-option.k-button:focus,
.k-drawer-option.k-button:hover {
	color: var(--color-black);
}

/* Nested drawers */
.k-drawer[data-nested="true"] {
	background: none;
}
</style>
