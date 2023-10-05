<template>
	<k-ui-examples>
		<h2 class="h4">State</h2>
		<k-ui-example label="data">
			<k-text>
				<p>
					<code>window.panel.drag.data</code> returns data for the currently
					dragged item
				</p>
				<k-code>{{ $panel.drag.data }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="isDragging">
			<k-text>
				<p>
					<code>window.panel.drag.isDragging</code> is true when there's
					currently an item being dragged.
				</p>
				<k-code>{{ $panel.drag.isDragging }}</k-code>
			</k-text>
		</k-ui-example>
		<k-ui-example label="type">
			<k-text>
				<p>
					<code>window.panel.drag.type</code> Returns the drag type (e.g. text)
				</p>
				<k-code>{{ $panel.drag.type || "&nbsp;" }}</k-code>
			</k-text>
		</k-ui-example>

		<h2 class="h4">Methods</h2>

		<k-ui-example label="start">
			<k-text>
				<k-ui-code language="js"
					>window.panel.drag.start(type, data);</k-ui-code
				>
			</k-text>
		</k-ui-example>
		<k-ui-example label="stop">
			<k-text>
				<k-ui-code language="js">window.panel.drag.stop();</k-ui-code>
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
