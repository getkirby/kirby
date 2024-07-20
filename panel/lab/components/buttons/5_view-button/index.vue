<template>
	<k-lab-examples>
		<k-lab-example label="Simple button">
			<k-view-button icon="info" text="A simple button" />
		</k-lab-example>
		<k-lab-example label="Dropdown: Fiber route">
			<k-view-button text="A dropdown" dropdown="a/route" />
		</k-lab-example>
		<k-lab-example label="Dropdown: Options array">
			<k-view-button
				icon="cog"
				:dropdown="[{ text: 'Option A' }, { text: 'Option B' }]"
			/>
		</k-lab-example>
		<k-lab-example label="Languages button">
			<k-view-languages-button
				:options="[{ text: 'English' }, '-', { text: 'Deutsch' }]"
				text="EN"
			/>
		</k-lab-example>
		<k-lab-example label="Preview button">
			<k-view-preview-button link="https://getkirby.com" />
		</k-lab-example>
		<k-lab-example label="Settings button">
			<k-view-settings-button dropdown="pages/sections+info" />
		</k-lab-example>
		<k-lab-example label="Status button">
			<k-view-status-button
				text="Draft"
				icon="status-draft"
				theme="negative-icon"
			/>
		</k-lab-example>
		<k-lab-example label="Theme button">
			<k-view-theme-button />
		</k-lab-example>
	</k-lab-examples>
</template>
