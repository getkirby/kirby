<template>
	<k-panel class="k-preview-view k-remote-preview-view">
		<header class="k-preview-view-header">
			<span class="k-remote-preview-view-title">
				<k-icon type="git-branch" /> {{ $t("version.changes") }}
			</span>

			<k-button-group layout="collapsed" class="k-preview-view-sizes">
				<k-button
					v-for="(button, size) in sizeButtons"
					:key="size"
					v-bind="button"
					@click="onSize(size)"
				/>
			</k-button-group>

			<k-button-group>
				<k-button
					:aria-checked="isPinned"
					:title="$t('preview.browser.pin')"
					:theme="isPinned ? 'info' : 'passive'"
					icon="pushpin"
					role="switch"
					size="sm"
					variant="filled"
					@click="togglePin"
				/>
				<k-button
					:responsive="true"
					:title="$t('back')"
					icon="cancel-small"
					size="sm"
					variant="filled"
					@click="exit"
				/>
			</k-button-group>
		</header>

		<main
			ref="grid"
			class="k-preview-view-grid"
			:style="`--preview-width: ${sizes[size].width}`"
		>
			<k-preview-browser
				ref="browser"
				:is-pinned="isPinned"
				:src="src.changes"
				@navigate="onBrowserNavigate"
				@pin="togglePin"
			/>
		</main>

		<k-panel-notifications />
	</k-panel>
</template>

<script>
import { Preview } from "./PreviewView.vue";

export default {
	mixins: [Preview],
	mounted() {
		// open op channel to preview window with form fields
		this.channel = new BroadcastChannel("preview$" + this.id);

		this.channel.addEventListener("message", (event) => {
			if (event.data.on === "host:changes") {
				return this.onChanges();
			}
			if (event.data.on === "host:view") {
				return this.onViewNavigate(event.data.url);
			}
			if (event.data.on === "remote:exit") {
				return this.exit();
			}
		});

		// when window is closed, let preview view know
		window.addEventListener("beforeunload", () => {
			this.announce("remote:exit");
			// in case this is a window reload, redirect to frontend
			// as the connection to the original preview view is lost
			window.setTimeout(() => (window.location = this.src.changes), 50);
		});
	},
	methods: {
		exit() {
			this.announce("remote:exit");
			window.close();
		},
		onBrowserNavigate({ browser = null, view = null }) {
			Preview.methods.onBrowserNavigate.call(this, { browser, view });
			this.announce("remote:browser", { browser, view });
		},
		async onViewNavigate(url) {
			Preview.methods.onViewNavigate.call(this, url + "/remote");
		},
		togglePin() {
			Preview.methods.togglePin.call(this);
			this.announce("remote:reload");
		}
	}
};
</script>

<style>
.k-remote-preview-view-title {
	display: flex;
	gap: var(--spacing-2);
	padding-inline: var(--button-padding);
}
</style>
