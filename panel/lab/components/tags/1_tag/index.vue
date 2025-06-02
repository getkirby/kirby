<template>
	<k-lab-examples>
		<k-lab-example label="Slot">
			<k-tag>Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Text">
			<k-tag text="Foo" />
		</k-lab-example>
		<k-lab-example label="HTML">
			<k-tag :html="true" text="Tag with <i>HTML</i>" />
		</k-lab-example>
		<k-lab-example label="Removable">
			<k-tag :removable="true" text="Foo" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-tag :disabled="true" :removable="true" text="Foo" />
		</k-lab-example>
		<k-lab-example :flex="true" label="Icon">
			<k-tag :image="{ icon: 'heart' }" />
			<k-tag :image="{ icon: 'heart' }" text="Foo" />
			<k-tag :image="{ icon: 'heart' }" :removable="true" text="Foo" />
			<k-tag
				:disabled="true"
				:image="{ icon: 'heart' }"
				:removable="true"
				text="Foo"
			/>
		</k-lab-example>
		<k-lab-example :flex="true" label="Image">
			<k-tag :image="{ src: 'https://picsum.photos/100/100/', crop: true }" />
			<k-tag :image="{ src: 'https://picsum.photos/100/100/', crop: true }" text="Foo" />
			<k-tag
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				text="Foo"
			>
			<k-tag
				:disabled="true"
				:image="{ src: 'https://picsum.photos/100/100/', crop: true }"
				:removable="true"
				text="Foo"
			/>
		</k-lab-example>
		<k-lab-example label="Too long">
			<k-tag
				:removable="true"
				text="Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas sed diam eget risus varius blandit sit amet non magna. Vivamus sagittis lacus vel augue laoreet rutrum faucibus dolor auctor."
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
