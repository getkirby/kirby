<template>
	<k-lab-examples>
		<h2 class="h4">Get current theme</h2>
		<k-lab-example label="panel.theme.current" :code="false">
			<k-text>
				<p><code>window.panel.theme.current</code> returns the current theme</p>
				<k-code>{{ $panel.theme.current }}</k-code>
			</k-text>
		</k-lab-example>

		<h2 class="h4">Set current theme</h2>
		<k-lab-example label="panel.theme.set(theme)" :code="false">
			<k-text>
				<p><code>window.panel.theme.set(theme)</code> sets the current theme</p>
				<k-code>window.panel.theme.set("light")
window.panel.theme.set("dark")</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="panel.theme.reset()" :code="false">
			<k-text>
				<p><code>window.panel.theme.reset()</code> resets the current theme. The Panel will then adhere to the OS settings.</p>
				<k-code>window.panel.theme.reset()</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
