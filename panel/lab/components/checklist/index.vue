<template>
	<k-lab-examples>
		<k-lab-example label="Checklist">
			<k-checklist>
				<li>Item A</li>
				<li>Item B</li>
				<li>Item C</li>
			</k-checklist>
		</k-lab-example>
		<k-lab-example label="Checklist (wrapping)">
			<k-checklist>
				<li>
					Etiam porta sem malesuada magna mollis euismod. Cras mattis
					consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla
					sed consectetur. Nullam quis risus eget urna mollis ornare vel eu leo.
				</li>
				<li>
					Etiam porta sem malesuada magna mollis euismod. Cras mattis
					consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla
					sed consectetur. Nullam quis risus eget urna mollis ornare vel eu leo.
				</li>
				<li>
					Etiam porta sem malesuada magna mollis euismod. Cras mattis
					consectetur purus sit amet fermentum. Aenean lacinia bibendum nulla
					sed consectetur. Nullam quis risus eget urna mollis ornare vel eu leo.
				</li>
			</k-checklist>
		</k-lab-example>
		<k-lab-example label="Checklist (theme: notice)">
			<k-checklist theme="notice">
				<li>Item A</li>
				<li>Item B</li>
				<li>Item C</li>
			</k-checklist>
		</k-lab-example>
		<k-lab-example label="Checklist (theme: negative)">
			<k-checklist theme="negative">
				<li>Item A</li>
				<li>Item B</li>
				<li>Item C</li>
			</k-checklist>
		</k-lab-example>
		<k-lab-example label="Checklist with a custom marker">
			<k-checklist style="--checklist-marker: &quot;💛&quot;">
				<li>Item A</li>
				<li>Item B</li>
				<li>Item C</li>
			</k-checklist>
		</k-lab-example>
		<k-lab-example label="Checklist with mixed markers">
			<k-checklist>
				<li data-theme="positive">Item A</li>
				<li data-theme="notice">Item B</li>
				<li data-theme="negative">Item C</li>
			</k-checklist>
		</k-lab-example>
	</k-lab-examples>
</template>
