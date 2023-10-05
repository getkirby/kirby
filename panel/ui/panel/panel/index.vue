<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="context">
			<k-text>
				<p>
					<code>window.panel.context</code> returns the current editing context
					(view, dialog, drawer)
				</p>
				<k-code>{{ $panel.context }}</k-code>
				<k-button-group>
					<k-button
						variant="filled"
						@click="
							$panel.dialog.open({
								component: 'k-text-dialog',
								props: {
									text: `
										<p>
											Opening a dialog should have changed the
											context to <code>dialog</code>.
										</p>
										<p>
											Close the dialog to see it switch back to
											<code>view</code>
										</p>
									`
								}
							})
						"
					>
						Open Dialog
					</k-button>
					<k-button
						variant="filled"
						@click="
							$panel.drawer.open({
								component: 'k-text-drawer',
								props: {
									icon: 'text',
									title: 'Drawer',
									text: `
										<p>
											Opening a drawer should have changed the
											context to <code>drawer</code>.
										</p>
										<p>
											Close the drawer to see it switch back to
											<code>view</code>
										</p>
									`
								}
							})
						"
					>
						Open Drawer
					</k-button>
				</k-button-group>
			</k-text>
		</k-ui-example>
		<k-ui-example label="debug">
			<k-text>
				<p>
					<code>window.panel.debug</code> returns current debug mode. It's a
					shortcut for <code>window.panel.config.debug</code>
				</p>
				<k-code>{{ $panel.debug }}</k-code>
				<k-toggle-input
					:value="$panel.config.debug"
					@input="$panel.config.debug = $event"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="direction">
			<k-text>
				<p>
					<code>window.panel.direction</code> returns the reading direction of
					the interface translation. It's a shortcut for
					<code>window.panel.translation.direction</code>. You should only
					change the direction for testing purposes. The direction is
					automatically changed when the translation changes.
				</p>
				<k-code>{{ $panel.direction }}</k-code>
				<k-button-group>
					<k-button
						icon="text-left"
						variant="filled"
						@click="$panel.translation.direction = 'ltr'"
					/>
					<k-button
						icon="text-right"
						variant="filled"
						@click="$panel.translation.direction = 'rtl'"
					/>
				</k-button-group>
			</k-text>
		</k-ui-example>
		<k-ui-example label="languages">
			<k-text>
				<p>
					<code>window.panel.languages</code> array with all installed languages
				</p>
				<k-code>{{ $panel.languages }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="multilang">
			<k-text>
				<p>
					<code>window.panel.multilang</code> true if multiple languages can be
					installed
				</p>
				<k-code>{{ $panel.multilang }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="searches">
			<k-text>
				<p>
					<code>window.panel.searches</code> object with all available searches
				</p>
				<k-ui-code language="js">{{ $panel.searches }}</k-ui-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="title">
			<k-text>
				<p>
					<code>window.panel.title</code> returns the current document title
				</p>
				<k-code>{{ $panel.title }}</k-code>
				<p>
					<code>window.panel.title</code> can also be used as a setter. It sets
					a new document title by combining the value with
					<code>window.panel.system.title</code>
				</p>
				<div style="display: flex; gap: 0.5rem">
					<k-input
						:value="title"
						placeholder="New title …"
						type="text"
						@input="title = $event"
					/>
					<k-button
						text="Set title"
						variant="filled"
						size="lg"
						@click="$panel.title = title"
					/>
				</div>
			</k-text>
		</k-ui-example>

		<h2 class="h4">Methods</h2>

		<k-ui-example label="error">
			<k-text>
				<k-ui-code language="js"
					>window.panel.error(error, openNotification = true)</k-ui-code
				>
				<p>Logs (and optionally displays) error an message</p>
				<k-button
					text="Notification"
					variant="filled"
					@click="$panel.error('Something went wrong')"
				/>
				<k-button
					text="Log"
					variant="filled"
					@click="$panel.error('Something went wrong', false)"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="get">
			<k-text>
				<k-ui-code language="js"
					>window.panel.get(url, options = {});</k-ui-code
				>
				<p>Sends a GET request</p>
				<k-button-group>
					<k-button text="GET: site" variant="filled" @click="get('site')" />
					<k-button text="GET: users" variant="filled" @click="get('users')" />
					<k-button text="GET: ui" variant="filled" @click="get('ui')" />
				</k-button-group>
			</k-text>
		</k-ui-example>
		<k-ui-example label="open">
			<k-text>
				<k-ui-code language="js"
					>window.panel.open(url, options = {});</k-ui-code
				>
				<p>
					Sends a GET request and updates the state. This will actually route to
					a new view if the response includes one.
				</p>
				<k-button-group>
					<k-button
						text="OPEN: site"
						variant="filled"
						@click="$panel.open('site')"
					/>
					<k-button
						text="OPEN: users"
						variant="filled"
						@click="$panel.open('users')"
					/>
					<k-button
						text="OPEN: account"
						variant="filled"
						@click="$panel.open('account')"
					/>
				</k-button-group>
			</k-text>
		</k-ui-example>
		<k-ui-example label="post">
			<k-text>
				<k-ui-code language="js"
					>window.panel.post(url, options = {});</k-ui-code
				>
				<p>Sends a POST request</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="redirect">
			<k-text>
				<k-ui-code language="js"
					>window.panel.redirect(url, options = {});</k-ui-code
				>
				<p>Full redirect to a different URL</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="request">
			<k-text>
				<k-ui-code language="js"
					>window.panel.request(url, options = {});</k-ui-code
				>
				<p>Low-level request handler</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="search">
			<k-text>
				<k-ui-code language="js">window.panel.search(type, query);</k-ui-code>
				<p>Sends a search request</p>
				<div style="display: flex; gap: 0.5rem">
					<k-input :value="search" type="search" @input="search = $event" />
					<k-button
						text="Search"
						variant="filled"
						size="lg"
						@click="openSearch"
					/>
				</div>
			</k-text>
		</k-ui-example>
		<k-ui-example label="set">
			<k-text>
				<k-ui-code language="js">window.panel.set(state);</k-ui-code>
				<p>Overwrites the Panel state</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="state">
			<k-text>
				<k-ui-code language="js">window.panel.state();</k-ui-code>
				<p>Returns the full Panel state including all modules</p>
				<k-button text="Test" variant="filled" @click="openState" />
			</k-text>
		</k-ui-example>
		<k-ui-example label="url">
			<k-text>
				<k-ui-code language="js"
					>window.panel.url(url = "", query = {}, origin);</k-ui-code
				>
				<p>URL builder for Panel URLs</p>
				<div style="display: flex; gap: 0.5rem">
					<k-input
						:value="url"
						placeholder="Path"
						type="text"
						@input="url = $event"
					/>
					<k-button
						text="Build URL"
						variant="filled"
						size="lg"
						@click="buildUrl"
					/>
				</div>
			</k-text>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {
			search: null,
			title: null,
			url: null
		};
	},
	methods: {
		buildUrl() {
			alert(this.$panel.url(this.url ?? ""));
		},
		async get(path) {
			this.$panel.dialog.open({
				component: "k-ui-output-dialog",
				props: {
					code: await this.$panel.get(path)
				}
			});
		},
		async openSearch() {
			this.$panel.dialog.open({
				component: "k-ui-output-dialog",
				props: {
					code: await this.$panel.search("pages", this.search)
				}
			});
		},
		openState() {
			this.$panel.dialog.open({
				component: "k-ui-output-dialog",
				props: {
					code: this.$panel.state()
				}
			});
		}
	}
};
</script>

<style>
.k-ui-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-ui-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
