<template>
	<k-ui-examples>
		<h2 class="h4">Methods</h2>

		<k-ui-example label="emit" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.events.emit(event, ...args);</k-ui-code
				>
			</k-text>
		</k-ui-example>
		<k-ui-example label="off" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.events.off(event, callback);</k-ui-code
				>
			</k-text>
		</k-ui-example>
		<k-ui-example label="on" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.events.on(event, callback);</k-ui-code
				>
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
