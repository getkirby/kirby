<template>
	<k-ui-examples>
		<k-ui-example label="simple">
			<k-header> Title </k-header>
		</k-ui-example>
		<k-ui-example label="editable">
			<k-header :editable="true"> Title </k-header>
		</k-ui-example>
		<k-ui-example label="buttons">
			<k-header :editable="true">
				Title
				<k-button-group slot="buttons">
					<k-button icon="open" variant="filled" />
					<k-button icon="cog" variant="filled" />
				</k-button-group>
			</k-header>
		</k-ui-example>
	</k-ui-examples>
</template>
