<template>
	<k-lab-examples>
		<h2 class="h4">Methods</h2>

		<k-lab-example label="emit" :code="false">
			<k-text>
				<k-lab-code language="js"
					>window.panel.events.emit(event, ...args);</k-lab-code
				>
			</k-text>
		</k-lab-example>
		<k-lab-example label="off" :code="false">
			<k-text>
				<k-lab-code language="js"
					>window.panel.events.off(event, callback);</k-lab-code
				>
			</k-text>
		</k-lab-example>
		<k-lab-example label="on" :code="false">
			<k-text>
				<k-lab-code language="js"
					>window.panel.events.on(event, callback);</k-lab-code
				>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-lab-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-lab-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
