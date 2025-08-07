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
		<k-lab-example label="Dropdown: State route">
			<k-view-button text="A dropdown" options="pages/sections+files" />
		</k-lab-example>
		<k-lab-example label="Dropdown: Options array">
			<k-view-button
				icon="cog"
				:options="[{ text: 'Option A' }, { text: 'Option B' }]"
			/>
		</k-lab-example>
		<k-lab-example label="Settings button">
			<k-settings-view-button icon="cog" options="pages/sections+info" />
		</k-lab-example>
		<k-lab-example label="Status button">
			<k-status-view-button
				text="Draft"
				icon="status-draft"
				theme="negative-icon"
			/>
		</k-lab-example>
		<k-lab-example label="Theme button">
			<k-theme-view-button />
		</k-lab-example>
	</k-lab-examples>
</template>
