<template>
	<k-lab-examples>
		<k-lab-example label="stack (gap: default)">
			<k-stack>
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
			</k-stack>
		</k-lab-example>
		<k-lab-example label="stack (gap: spacing-12)">
			<k-stack gap="var(--spacing-12)">
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
			</k-stack>
		</k-lab-example>
		<k-lab-example label="stack (direction: row)">
			<k-stack direction="row">
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
			</k-stack>
		</k-lab-example>
		<k-lab-example label="stack (direction: row, justify: space-between)">
			<k-stack direction="row" justify="space-between">
				<k-box theme="white" style="width: auto">Test Box</k-box>
				<k-box theme="white" style="width: auto">Test Box</k-box>
			</k-stack>
		</k-lab-example>
		<k-lab-example label="stack (direction: row, align: center)">
			<k-stack direction="row" align="center">
				<k-box theme="white">Line 1<br />Line 2</k-box>
				<k-box theme="white">Test Box</k-box>
				<k-box theme="white">Test Box</k-box>
			</k-stack>
		</k-lab-example>
	</k-lab-examples>
</template>
