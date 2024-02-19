<template>
	<k-lab-examples>
		<h2 class="h4">State</h2>
		<k-lab-example label="data" :code="false">
			<k-text>
				<p>
					<code>window.panel.drag.data</code> returns data for the currently
					dragged item
				</p>
				<k-code>{{ $panel.drag.data }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="isDragging" :code="false">
			<k-text>
				<p>
					<code>window.panel.drag.isDragging</code> is true when there's
					currently an item being dragged.
				</p>
				<k-code>{{ $panel.drag.isDragging }}</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="type" :code="false">
			<k-text>
				<p>
					<code>window.panel.drag.type</code> Returns the drag type (e.g. text)
				</p>
				<k-code>{{ $panel.drag.type || "&nbsp;" }}</k-code>
			</k-text>
		</k-lab-example>

		<h2 class="h4">Methods</h2>

		<k-lab-example label="start" :code="false">
			<k-text>
				<k-code language="js">window.panel.drag.start(type, data);</k-code>
			</k-text>
		</k-lab-example>
		<k-lab-example label="stop" :code="false">
			<k-text>
				<k-code language="js">window.panel.drag.stop();</k-code>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>
