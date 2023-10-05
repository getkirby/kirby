<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="debug">
			<k-text>
				<p>
					<code>window.panel.config.debug</code> returns the current debug mode.
					Use <code>window.panel.debug</code> as shortcut.
				</p>
				<k-code>{{ $panel.config.debug }}</k-code>
				<k-toggle-input
					:value="$panel.config.debug"
					@input="$panel.config.debug = $event"
				/>
			</k-text>
		</k-ui-example>
		<k-ui-example label="kirbytext">
			<k-text>
				<p>
					<code>window.panel.config.kirbytext</code> If true, the textarea will
					insert code as kirbytext instead of markdown.
				</p>
				<k-code>{{ $panel.config.kirbytext }}</k-code>
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
