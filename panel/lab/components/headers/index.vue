<template>
	<k-lab-examples>
		<k-lab-example label="simple">
			<k-header> Title </k-header>
		</k-lab-example>
		<k-lab-example label="editable">
			<k-header :editable="true"> Title </k-header>
		</k-lab-example>
		<k-lab-example label="buttons">
			<k-header :editable="true">
				Title
				<k-button-group slot="buttons">
					<k-button icon="open" variant="filled" />
					<k-button icon="cog" variant="filled" />
				</k-button-group>
			</k-header>
		</k-lab-example>
	</k-lab-examples>
</template>
