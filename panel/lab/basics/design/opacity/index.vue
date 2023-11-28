<template>
	<k-lab-examples>
		<k-text>
			Exposed as custom CSS properties, e.g.
			<code>opacity: var(--opacity-disabled)</code>
		</k-text>

		<k-lab-example label="--opacity-disabled" :code="false">
			<k-box theme="white" style="opacity: var(--opacity-disabled)">
				opacity-disabled
			</k-box>
		</k-lab-example>
	</k-lab-examples>
</template>
