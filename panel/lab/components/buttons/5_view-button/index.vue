<template>
	<k-lab-examples>
		<k-lab-example label="Simple button">
			<k-view-button
				icon="info"
				text="A simple button"
				link="https://getkirby.com"
				target="_blank"
			/>
		</k-lab-example>
		<k-lab-example label="Dropdown: Fiber route">
			<k-view-button text="A dropdown" options="pages/sections+files" />
		</k-lab-example>
		<k-lab-example label="Dropdown: Options array">
			<k-view-button
				icon="cog"
				:options="[{ text: 'Option A' }, { text: 'Option B' }]"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
