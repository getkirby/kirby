<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="code">
			<k-text>
				<p>
					<code>window.panel.translation.code</code> returns the current
					language code for the active translation
				</p>
				<k-code>{{ $panel.translation.code }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="date">
			<k-text>
				<p>
					<code>window.panel.translation.data</code> returns the entire object
					with all messages and other infor about the translation
				</p>
				<k-ui-code language="js">{{ $panel.translation.data }}</k-ui-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="direction">
			<k-text>
				<p>
					<code>window.panel.translation.direction</code> returns the reading
					direction (ltr or rtl)
				</p>
				<k-code>{{ $panel.translation.direction }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="name">
			<k-text>
				<p>
					<code>window.panel.translation.name</code> returns the name of the
					translation
				</p>
				<k-code>{{ $panel.translation.name }}</k-code>
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
