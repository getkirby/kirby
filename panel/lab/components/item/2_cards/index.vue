<template>
	<k-lab-examples class="k-card-items-example">
		<k-lab-example label="Card Item">
			<k-item layout="cards" text="This is a nice item" />
		</k-lab-example>
		<k-lab-example label="Text & Info">
			<k-item layout="cards" info="With some info" text="This is a nice item" />
		</k-lab-example>
		<k-lab-example label="Image, Text & Info">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some info"
				layout="cards"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Link">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some info"
				layout="cards"
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
				layout="cards"
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
				layout="cards"
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
				layout="cards"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				layout="cards"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting & selected">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				:selected="true"
				layout="cards"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selecting & not selectable">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				:selectable="false"
				layout="cards"
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
				layout="cards"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Selectmode: single">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				:selecting="true"
				selectmode="single"
				layout="cards"
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
				layout="cards"
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
				layout="cards"
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
				layout="cards"
				info="With some info"
				text="This is a nice item"
			/>
		</k-lab-example>
		<k-lab-example label="Theme: disabled">
			<k-item
				:image="{ src: 'https://picsum.photos/800/600' }"
				info="With some info"
				layout="cards"
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
				layout="cards"
				text="This is a nice item with a rather long title that should wrap"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<style>
.k-card-items-example .k-item {
	max-width: 30rem;
}
</style>
