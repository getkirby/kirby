<template>
	<k-lab-examples>
		<k-lab-example label="default">
			<k-button-group>
				<k-button>A</k-button>
				<k-button>B</k-button>
				<k-button>C</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="filled">
			<k-button-group>
				<k-button variant="filled">A</k-button>
				<k-button variant="filled">B</k-button>
				<k-button variant="filled">C</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="mixed">
			<k-button-group>
				<k-button variant="filled">A</k-button>
				<k-button variant="filled">B</k-button>
				<k-button>C</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example :flex="true" label="layout: collapsed">
			<k-button-group layout="collapsed">
				<k-button variant="filled">A</k-button>
				<k-button variant="filled">B</k-button>
				<k-button variant="filled">C</k-button>
			</k-button-group>
			<k-button-group layout="collapsed">
				<k-button variant="filled" icon="angle-left" />
				<k-button variant="filled">1/12</k-button>
				<k-button variant="filled" icon="angle-right" />
			</k-button-group>
			<k-button-group layout="collapsed">
				<k-button variant="filled" icon="edit">Edit</k-button>
				<k-button
					variant="filled"
					icon="dots"
					@click="$refs.dropdown.toggle()"
				/>
				<k-dropdown-content ref="dropdown" align-x="end">
					<k-dropdown-item icon="edit">Edit</k-dropdown-item>
					<k-dropdown-item icon="copy">Duplicate</k-dropdown-item>
					<hr />
					<k-dropdown-item icon="trash">Delete</k-dropdown-item>
				</k-dropdown-content>
			</k-button-group>
		</k-lab-example>
	</k-lab-examples>
</template>
