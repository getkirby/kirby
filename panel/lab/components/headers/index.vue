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

				<template #buttons>
					<k-button-group>
						<k-button icon="open" variant="filled" />
						<k-button icon="cog" variant="filled" />
					</k-button-group>
				</template>
			</k-header>
		</k-lab-example>
		<k-lab-example label="long title & many buttons">
			<k-header :editable="true">
				This is a rather long title that still should not cause any issues

				<template #buttons>
					<k-button-group>
						<k-button icon="open" variant="filled" />
						<k-button icon="cog" variant="filled" />
						<k-button icon="heart" text="Favorite" variant="filled" />
						<k-button icon="star" text="Bookmark" variant="filled" />
					</k-button-group>
				</template>
			</k-header>
		</k-lab-example>
	</k-lab-examples>
</template>
