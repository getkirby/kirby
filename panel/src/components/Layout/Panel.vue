<template>
	<div
		:data-dragging="$store.state.drag"
		:data-loading="$panel.isLoading"
		:data-language="language"
		:data-language-default="defaultLanguage"
		:data-role="role"
		:data-translation="$translation.code"
		:data-user="user"
		:dir="dir"
		class="k-panel"
	>
		<slot />

		<!-- Fiber dialogs -->
		<template v-if="$store.state.dialog && $store.state.dialog.props">
			<k-fiber-dialog v-bind="dialog" />
		</template>

		<!-- Fatal iframe -->
		<k-fatal
			v-if="$panel.notification.isFatal && $panel.notification.isOpen"
			:html="$panel.notification.message"
		/>

		<!-- Offline warning -->
		<k-offline-warning v-if="$system.isLocal === false" />

		<!-- Icons -->
		<k-icons />

		<portal-target class="k-drawer-portal k-portal" name="drawer" multiple />
		<portal-target class="k-dialog-portal k-portal" name="dialog" multiple />
		<portal-target class="k-overlay-portal k-portal" name="overlay" multiple />
	</div>
</template>

<script>
export default {
	computed: {
		defaultLanguage() {
			return this.$language ? this.$language.default : false;
		},
		dialog() {
			return this.$helper.clone(this.$store.state.dialog);
		},
		dir() {
			return this.$translation.direction;
		},
		language() {
			return this.$language ? this.$language.code : null;
		},
		role() {
			return this.$user ? this.$user.role : null;
		},
		user() {
			return this.$user ? this.$user.id : null;
		}
	},
	watch: {
		dir: {
			handler() {
				/**
				 * Some elements – i.e. drag ghosts -
				 * are injected into the body and not the panel div.
				 * They need the dir to be displayed correctly
				 */
				document.body.dir = this.dir;
			},
			immediate: true
		}
	},
	created() {
		this.$events.$on("drop", this.drop);
	},
	destroyed() {
		this.$events.$off("drop", this.drop);
	},
	methods: {
		drop() {
			// remove any drop data from the store
			this.$store.dispatch("drag", null);
		}
	}
};
</script>

<style>
.k-panel[data-loading="true"] {
	animation: LoadingCursor 0.5s;
}
.k-panel[data-loading="true"]::after,
.k-panel[data-dragging="true"] {
	user-select: none;
}
</style>
