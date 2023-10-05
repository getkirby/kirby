<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="debug">
			<k-text>
				<p>
					<code>window.panel.system.ascii</code> returns the ascii conversion
					table.
				</p>
				<k-ui-code language="js">{{ $panel.system.ascii }}</k-ui-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="csrf">
			<k-text>
				<p>
					<code>window.panel.system.csrf</code> returns the current CSRF token
				</p>
				<k-code>{{ $panel.system.csrf }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="isLocal">
			<k-text>
				<p>
					<code>window.panel.system.isLocal</code> If true, the Panel assumes it
					is installed on a local environment
				</p>
				<k-code>{{ $panel.system.isLocal }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="locales">
			<k-text>
				<p>
					<code>window.panel.system.locales</code> returns the system locales
				</p>
				<k-ui-code language="js">{{ $panel.system.locales }}</k-ui-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="slugs">
			<k-text>
				<p>
					<code>window.panel.system.slugs</code> returns all basic slug rules
					for the current language
				</p>
				<k-code>{{ $panel.system.slugs }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="title">
			<k-text>
				<p><code>window.panel.system.title</code> returns the site title</p>
				<k-code>{{ $panel.system.title }}</k-code>
			</k-text>
		</k-ui-example>
	</k-ui-examples>
</template>

<style>
.k-ui-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-ui-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
