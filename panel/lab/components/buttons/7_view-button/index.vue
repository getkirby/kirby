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
			<k-view-button text="A dropdown" options="account" />
		</k-lab-example>
		<k-lab-example label="Dropdown: Options array">
			<k-view-button
				icon="cog"
				:options="[{ text: 'Option A' }, { text: 'Option B' }]"
			/>
		</k-lab-example>
		<k-lab-example label="Settings button">
			<k-view-button
				class="k-settings-view-button"
				icon="cog"
				options="account"
				title="Settings"
			/>
		</k-lab-example>
		<k-lab-example label="Status button">
			<k-view-button
				class="k-status-view-button"
				icon="status-draft"
				style="--icon-size: 15px"
				text="Draft"
				theme="negative-icon"
				title="Status: Draft"
			/>
		</k-lab-example>
		<k-lab-example label="Theme button">
			<k-theme-view-button />
		</k-lab-example>
	</k-lab-examples>
</template>
