<template>
	<k-lab-examples>
		<k-lab-example label="List Item">
			<k-item text="This is a nice item" />
		</k-lab-example>
		<k-lab-example label="Text & Info">
			<k-item info="With some info" text="This is a nice item" />
		</k-lab-example>
		<k-lab-example label="Image, Text & Info">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Link">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some info"
				link="https://getkirby.com"
				text="This is a nice item"
				target="_blank"
			/>
		</k-lab-example>
		<k-lab-example label="Sortable">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:sortable="true"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Options">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:options="[
					{
						text: 'Edit',
						icon: 'edit'
					},
					{
						text: 'Duplicate',
						icon: 'copy'
					},
					'-',
					{
						text: 'Delete',
						icon: 'trash'
					}
				]"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Buttons">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:buttons="[
					{
						icon: 'edit'
					},
					{
						icon: 'trash'
					}
				]"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting & selected">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				:selected="true"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting & not selectable">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				:selectable="false"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting & not selectable, but selected">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				:selectable="false"
				:selected="true"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selectmode: single">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				selectmode="single"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selectmode: single & selected">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				selectmode="single"
				:selected="true"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selectmode: single & not selectable">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				selectmode="single"
				:selectable="false"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selectmode: single & not selectable, but selected">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				selectmode="single"
				:selectable="false"
				:selected="true"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Theme: disabled">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some info"
				text="This is a nice item"
				theme="disabled"
			/>
		</k-lab-example>
		<k-lab-example label="Wrap for long title/info">
			<k-item
				:buttons="[
					{
						icon: 'edit'
					},
					{
						icon: 'trash'
					}
				]"
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some more information than usual"
				text="This is a nice item with a rather long title that should wrap"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
