<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="debug" :code="false">
			<k-text>
				<p>
					<code>window.panel.system.ascii</code> returns the ascii conversion
					table.
				</p>
				<k-code language="js">{{ $panel.system.ascii }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="csrf" :code="false">
			<k-text>
				<p>
					<code>window.panel.system.csrf</code> returns the current CSRF token
				</p>
				<k-code>{{ $panel.system.csrf }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="isLocal" :code="false">
			<k-text>
				<p>
					<code>window.panel.system.isLocal</code> If true, the Panel assumes it
					is installed on a local environment
				</p>
				<k-code>{{ $panel.system.isLocal }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="locales" :code="false">
			<k-text>
				<p>
					<code>window.panel.system.locales</code> returns the system locales
				</p>
				<k-code language="js">{{ $panel.system.locales }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="slugs" :code="false">
			<k-text>
				<p>
					<code>window.panel.system.slugs</code> returns all basic slug rules
					for the current language
				</p>
				<k-code>{{ $panel.system.slugs }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="title" :code="false">
			<k-text>
				<p><code>window.panel.system.title</code> returns the site title</p>
				<k-code>{{ $panel.system.title }}</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
