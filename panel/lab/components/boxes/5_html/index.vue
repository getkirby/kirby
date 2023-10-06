<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="HTML">
			<k-box theme="info" text="Without <strong>HTML support</strong>" />
			<k-box
				:html="true"
				theme="info"
				text="With <strong>HTML support</strong>"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
